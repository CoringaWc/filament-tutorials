import { driver } from 'driver.js'
import '../css/filament-tutorials.css'

const runtimeSelector = '[data-filament-tutorials-runtime]'
const launcherSelector = '[data-filament-tutorials-launcher]'
const modalSelector = '[aria-modal="true"]:not(.filament-tutorials-popover)'
const optionalTargetTimeout = 80
const actionTargetTimeout = 10000
let activeDriver = null
let alpineComponentRegistered = false
let startingTutorial = false

const destroyActiveDriver = () => {
  if (!activeDriver) {
    return
  }

  activeDriver.destroy()
  activeDriver = null
}

const elementAndAncestorsAreVisible = (element) => {
  for (let currentElement = element; currentElement; currentElement = currentElement.parentElement) {
    if (currentElement.hasAttribute('hidden') || currentElement.getAttribute('aria-hidden') === 'true') {
      return false
    }

    const styles = window.getComputedStyle(currentElement)

    if (
      styles.display === 'none'
      || styles.visibility === 'hidden'
      || styles.visibility === 'collapse'
      || Number.parseFloat(styles.opacity) === 0
    ) {
      return false
    }
  }

  return true
}

const hasVisibleBox = (element) => {
  if (!elementAndAncestorsAreVisible(element)) {
    return false
  }

  const rect = element.getBoundingClientRect()

  return rect.width > 0 && rect.height > 0
}

const elementOrDescendantHasVisibleBox = (element) => {
  if (hasVisibleBox(element)) {
    return true
  }

  const descendants = document.createTreeWalker(element, NodeFilter.SHOW_ELEMENT)

  while (descendants.nextNode()) {
    if (hasVisibleBox(descendants.currentNode)) {
      return true
    }
  }

  return false
}

const visibleElement = (selector) => Array.from(document.querySelectorAll(selector))
  .find(hasVisibleBox) ?? null

const visibleStructuralElement = (selector) => Array.from(document.querySelectorAll(selector))
  .find((element) => elementAndAncestorsAreVisible(element) && elementOrDescendantHasVisibleBox(element)) ?? null

const waitForNextPaint = () => new Promise((resolve) => {
  let completed = false
  const finish = () => {
    if (completed) {
      return
    }

    completed = true
    window.clearTimeout(timeoutId)
    resolve()
  }
  const timeoutId = window.setTimeout(finish, 100)

  window.requestAnimationFrame(() => window.requestAnimationFrame(finish))
})

const waitForElement = (selector, timeout = 2500, findVisibleElement = visibleElement) => {
  const existingElement = findVisibleElement(selector)

  if (existingElement) {
    return Promise.resolve(existingElement)
  }

  return new Promise((resolve, reject) => {
    const timeoutId = window.setTimeout(() => {
      observer.disconnect()
      reject(new Error(`Tutorial target not found: ${selector}`))
    }, timeout)

    const observer = new MutationObserver(() => {
      const element = findVisibleElement(selector)

      if (!element) {
        return
      }

      window.clearTimeout(timeoutId)
      observer.disconnect()
      resolve(element)
    })

    observer.observe(document.documentElement, {
      attributes: true,
      childList: true,
      subtree: true,
    })
  })
}

const waitForFirstVisibleStep = (candidateSteps, timeout = 2500) => {
  const visibleCandidate = candidateSteps.find((candidate) => visibleElement(candidate.step.selector))

  if (visibleCandidate) {
    return Promise.resolve(visibleCandidate)
  }

  return new Promise((resolve) => {
    const timeoutId = window.setTimeout(() => {
      observer.disconnect()
      resolve(null)
    }, timeout)

    const observer = new MutationObserver(() => {
      const candidate = candidateSteps.find((item) => visibleElement(item.step.selector))

      if (!candidate) {
        return
      }

      window.clearTimeout(timeoutId)
      observer.disconnect()
      resolve(candidate)
    })

    observer.observe(document.documentElement, {
      attributes: true,
      childList: true,
      subtree: true,
    })
  })
}

const visibleModal = () => visibleStructuralElement(modalSelector)

const clickSelector = async (selector) => {
  const element = await waitForElement(selector)
  const clickableElement = element.matches('a, button, input, select, textarea, [role="button"]')
    ? element
    : element.closest('a, button, [role="button"]') ?? element.querySelector('a, button, [role="button"]') ?? element

  clickableElement.click()
}

const runAction = async (action) => {
  const parameters = action.parameters ?? {}

  if (action.action === 'click') {
    await clickSelector(parameters.selector)

    return
  }

  if (action.action === 'modal.open') {
    if (visibleModal()) {
      await waitForNextPaint()

      return
    }

    await clickSelector(parameters.selector ?? `[data-tour="${parameters.trigger}"]`)
    await waitForElement(modalSelector, actionTargetTimeout, visibleStructuralElement)
    await waitForNextPaint()

    return
  }

  if (action.action === 'dropdown.open') {
    await clickSelector(parameters.selector)

    return
  }

  if (action.action === 'collapsible.open') {
    const panel = document.querySelector(parameters.panel)

    if (panel && !panel.hasAttribute('hidden') && panel.getAttribute('aria-hidden') !== 'true') {
      return
    }

    await clickSelector(parameters.trigger)

    return
  }

  if (action.action === 'profile-menu.open') {
    if (parameters.selector) {
      await clickSelector(parameters.selector)
    }

    return
  }

  if (action.action === 'wait-for') {
    await waitForElement(parameters.selector, actionTargetTimeout)

    return
  }

  if (action.action === 'hide') {
    document.querySelector(parameters.selector)?.setAttribute('hidden', '')

    return
  }

  if (action.action === 'sidebar.open') {
    if (parameters.selector) {
      await clickSelector(parameters.selector)

      return
    }

    window.Alpine?.store('sidebar')?.open?.()
    document.dispatchEvent(new CustomEvent('filament-tutorials:open-sidebar'))
  }

  if (action.action === 'sidebar.opened') {
    return
  }
}

const actionKey = (action) => JSON.stringify({
  action: action.action,
  parameters: action.parameters ?? {},
})

const actionCanBePreparedOnce = (action) => action.action === 'modal.open'

const beforeActionKey = (step) => JSON.stringify((step.before ?? []).map((action) => actionKey(action)))

const runBeforeActions = async (step, preparedActionKeys = null) => {
  for (const action of step.before ?? []) {
    const key = actionKey(action)

    if (actionCanBePreparedOnce(action) && preparedActionKeys?.has(key)) {
      continue
    }

    await runAction(action)

    if (actionCanBePreparedOnce(action)) {
      preparedActionKeys?.add(key)
    }
  }
}

const runAfterActions = async (step) => {
  for (const action of step.after ?? []) {
    await runAction(action)
  }
}

const waitForStepTarget = async (step) => {
  if (!step?.selector) {
    return false
  }

  try {
    await waitForElement(step.selector, step.optional ? optionalTargetTimeout : 2500)

    return true
  } catch (error) {
    if (step.optional) {
      return false
    }

    throw error
  }
}

const dismissalReminderStep = (runtime) => {
  const reminder = runtimePayload(runtime).dismissalReminder

  if (!reminder?.enabled || !reminder?.selector) {
    return null
  }

  return {
    key: reminder.stepKey ?? 'reopen-page-tutorial',
    selector: reminder.selector,
    title: reminder.title,
    description: reminder.description,
    before: [],
    after: [],
    dismissalReminder: true,
  }
}

const tutorialSteps = (tutorial) => (tutorial.steps ?? []).filter((step) => step.selector)

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
  '&': '&amp;',
  '<': '&lt;',
  '>': '&gt;',
  '"': '&quot;',
  "'": '&#039;',
}[character]))

const driverStep = (step) => ({
  element: step.selector,
  popover: {
    title: escapeHtml(step.title),
    description: escapeHtml(step.description),
  },
})

const driverSteps = (steps) => steps.map((step) => driverStep(step))

const stepsWithFirstDismissalLayout = (steps, reminderStep) => {
  if (!reminderStep || steps.length === 0) {
    return steps
  }

  return steps.map((step, index) => {
    if (index !== 0) {
      return step
    }

    return {
      ...step,
      popover: {
        ...step.popover,
        showButtons: ['close', 'next'],
      },
    }
  })
}

const addDismissalReminderButton = (popover, label, onClick) => {
  if (popover.footerButtons.querySelector('[data-filament-tutorials-skip]')) {
    return
  }

  const button = document.createElement('button')
  button.type = 'button'
  button.textContent = label
  button.dataset.filamentTutorialsSkip = 'true'
  button.classList.add('driver-popover-footer-btn', 'filament-tutorials-skip-btn')
  button.addEventListener('click', onClick)

  popover.footerButtons.prepend(button)
}

const removeDismissalReminderButton = (popover) => {
  popover.footerButtons.querySelector('[data-filament-tutorials-skip]')?.remove()
}

const persistTutorialProgress = (runtime, tutorial, event, step = null, stepIndex = null, stepCount = null) => {
  const progress = runtimePayload(runtime).progress

  if (!progress?.endpoint) {
    return
  }

  const headers = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  }

  if (progress.csrfToken) {
    headers['X-CSRF-TOKEN'] = progress.csrfToken
  }

  window.fetch(progress.endpoint, {
    method: 'POST',
    credentials: 'same-origin',
    headers,
    body: JSON.stringify({
      panel_id: progress.panelId,
      tutorial_key: tutorial.key,
      event,
      step_key: step?.key ?? null,
      step_index: stepIndex,
      metadata: {
        source: 'runtime',
        step_count: stepCount ?? tutorial.steps?.length ?? 0,
      },
    }),
  }).catch((error) => console.error(error))
}

const interactiveSelector = [
  'a',
  'button',
  'input',
  'select',
  'textarea',
  'summary',
  '[role="button"]',
  '[role="menuitem"]',
  '[role="tab"]',
].join(', ')

const driverTargetAriaSelector = [
  '.driver-active-element[aria-haspopup]',
  '.driver-active-element[aria-expanded]',
  '.driver-active-element[aria-controls="driver-popover-content"]',
  '[data-tour][aria-haspopup="dialog"]',
  '[data-tour][aria-expanded="true"]',
  '[data-tour][aria-controls="driver-popover-content"]',
].join(', ')

const normalizeDriverTargetAria = (element = null) => {
  const elements = new Set(document.querySelectorAll(driverTargetAriaSelector))

  if (element) {
    elements.add(element)
  }

  elements.forEach((target) => {
    if (!target || target.matches(interactiveSelector)) {
      return
    }

    target.removeAttribute('aria-expanded')
    target.removeAttribute('aria-haspopup')
    target.removeAttribute('aria-controls')
  })
}

const startTutorial = async (runtime, tutorial) => {
  destroyActiveDriver()

  const launcher = document.querySelector(launcherSelector)
  const payload = runtimePayload(runtime)
  const labels = payload.labels
  const escapedLabels = {
    done: escapeHtml(labels.done),
    next: escapeHtml(labels.next),
    previous: escapeHtml(labels.previous),
    progress: escapeHtml(labels.progress),
  }
  const dismissalReminder = payload.dismissalReminder
  const originalStepCount = tutorialSteps(tutorial).length
  const reminderStep = dismissalReminderStep(runtime)
  let activeSteps = tutorialSteps(tutorial)
  let steps = stepsWithFirstDismissalLayout(driverSteps(activeSteps), reminderStep)
  let dismissalReminderActive = false
  let navigating = false

  if (steps.length === 0) {
    return
  }

  let firstStepIndex = 0

  while (activeSteps[firstStepIndex]) {
    await runBeforeActions(activeSteps[firstStepIndex])

    if (await waitForStepTarget(activeSteps[firstStepIndex])) {
      break
    }

    firstStepIndex += 1
  }

  if (!activeSteps[firstStepIndex]) {
    return
  }

  if (firstStepIndex > 0) {
    activeSteps = activeSteps.slice(firstStepIndex)
    steps = stepsWithFirstDismissalLayout(driverSteps(activeSteps), reminderStep)
  }

  persistTutorialProgress(runtime, tutorial, 'started', activeSteps[0], 0, originalStepCount)

  const ensureDismissalReminderIndex = async (currentDriver) => {
    const existingIndex = activeSteps.findIndex((step) => step.dismissalReminder)

    if (existingIndex >= 0) {
      return existingIndex
    }

    if (!reminderStep) {
      return -1
    }

    await runBeforeActions(reminderStep)

    if (!await waitForStepTarget(reminderStep)) {
      return -1
    }

    dismissalReminderActive = true
    activeSteps = [...activeSteps, reminderStep]
    steps = stepsWithFirstDismissalLayout(driverSteps(activeSteps), reminderStep)
    steps[steps.length - 1] = {
      ...steps[steps.length - 1],
      popover: {
        ...steps[steps.length - 1].popover,
        nextBtnText: escapedLabels.done,
        showButtons: ['next'],
      },
    }

    currentDriver.setConfig({
      ...currentDriver.getConfig(),
      steps,
    })

    return activeSteps.length - 1
  }

  const moveToAvailableStep = async (currentDriver, startIndex, direction = 1) => {
    const preparedActionKeys = new Set()

    for (
      let nextIndex = startIndex;
      nextIndex >= 0 && nextIndex < activeSteps.length;
      nextIndex += direction
    ) {
      const nextStep = activeSteps[nextIndex]

      await runBeforeActions(nextStep, preparedActionKeys)

      if (direction > 0 && nextStep.optional && (nextStep.before?.length ?? 0) > 0) {
        const sharedBeforeKey = beforeActionKey(nextStep)
        const candidateSteps = []
        let lastCandidateIndex = nextIndex

        for (
          let candidateIndex = nextIndex;
          candidateIndex < activeSteps.length;
          candidateIndex += 1
        ) {
          const candidateStep = activeSteps[candidateIndex]

          if (beforeActionKey(candidateStep) !== sharedBeforeKey) {
            break
          }

          candidateSteps.push({
            index: candidateIndex,
            step: candidateStep,
          })
          lastCandidateIndex = candidateIndex

          if (!candidateStep.optional) {
            break
          }
        }

        const visibleCandidate = await waitForFirstVisibleStep(candidateSteps)

        if (visibleCandidate) {
          currentDriver.moveTo(visibleCandidate.index)
          window.requestAnimationFrame(() => normalizeDriverTargetAria())

          return true
        }

        const requiredCandidate = candidateSteps.find((candidate) => !candidate.step.optional)

        if (requiredCandidate) {
          await waitForStepTarget(requiredCandidate.step)
          currentDriver.moveTo(requiredCandidate.index)
          window.requestAnimationFrame(() => normalizeDriverTargetAria())

          return true
        }

        nextIndex = lastCandidateIndex

        continue
      }

      if (!await waitForStepTarget(nextStep)) {
        continue
      }

      currentDriver.moveTo(nextIndex)
      window.requestAnimationFrame(() => normalizeDriverTargetAria())

      return true
    }

    return false
  }

  const showDismissalReminder = async (currentDriver) => {
    const activeIndex = currentDriver.getActiveIndex() ?? 0
    const activeStep = activeSteps[activeIndex]

    if (activeStep?.dismissalReminder || dismissalReminderActive) {
      return false
    }

    const reminderIndex = await runAfterActions(activeStep)
      .then(() => ensureDismissalReminderIndex(currentDriver))

    if (reminderIndex < 0) {
      return false
    }

    currentDriver.moveTo(reminderIndex)
    window.requestAnimationFrame(() => normalizeDriverTargetAria())

    return true
  }

  const finishDismissedTutorial = (currentDriver) => {
    const activeIndex = currentDriver.getActiveIndex() ?? 0
    const activeStep = activeSteps[activeIndex]

    runAfterActions(activeStep)
      .finally(() => {
        persistTutorialProgress(runtime, tutorial, 'dismissed', activeStep, activeIndex, originalStepCount)
        currentDriver.destroy()
      })
  }

  activeDriver = driver({
    allowClose: true,
    animate: !window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    doneBtnText: escapedLabels.done,
    nextBtnText: escapedLabels.next,
    prevBtnText: escapedLabels.previous,
    progressText: escapedLabels.progress,
    showButtons: ['close', 'previous', 'next'],
    showProgress: true,
    popoverClass: 'filament-tutorials-popover',
    steps,
    onPopoverRender: (popover, { driver: currentDriver }) => {
      const shouldShowDismissalReminder = Boolean(reminderStep && (currentDriver.getActiveIndex() ?? 0) === 0)

      popover.wrapper.classList.toggle(
        'filament-tutorials-has-dismissal-reminder',
        shouldShowDismissalReminder,
      )

      if (dismissalReminder?.skipLabel) {
        popover.closeButton.setAttribute('aria-label', dismissalReminder.skipLabel)
        popover.closeButton.setAttribute('title', dismissalReminder.skipLabel)
      }

      if (!shouldShowDismissalReminder) {
        removeDismissalReminderButton(popover)

        return
      }

      addDismissalReminderButton(popover, dismissalReminder.skipLabel, () => {
        showDismissalReminder(currentDriver)
          .catch((error) => console.error(error))
      })
    },
    onNextClick: (_element, _step, { driver: currentDriver }) => {
      if (navigating) {
        return
      }

      navigating = true

      const activeIndex = currentDriver.getActiveIndex() ?? 0
      const activeStep = activeSteps[activeIndex]

      runAfterActions(activeStep)
        .then(() => moveToAvailableStep(currentDriver, activeIndex + 1))
        .then((moved) => {
          if (!moved) {
            currentDriver.destroy()
          }
        })
        .catch((error) => console.error(error))
        .finally(() => {
          navigating = false
        })
    },
    onPrevClick: (_element, _step, { driver: currentDriver }) => {
      if (navigating) {
        return
      }

      navigating = true

      moveToAvailableStep(currentDriver, (currentDriver.getActiveIndex() ?? 0) - 1, -1)
        .catch((error) => console.error(error))
        .finally(() => {
          navigating = false
        })
    },
    onCloseClick: (_element, _step, { driver: currentDriver }) => {
      showDismissalReminder(currentDriver)
        .then((shown) => {
          if (shown) {
            return
          }

          finishDismissedTutorial(currentDriver)
        })
        .catch((error) => console.error(error))
    },
    onDoneClick: (_element, _step, { driver: currentDriver }) => {
      const activeIndex = currentDriver.getActiveIndex() ?? activeSteps.length - 1
      const activeStep = activeSteps[activeIndex]
      const event = activeStep?.dismissalReminder && dismissalReminderActive ? 'dismissed' : 'completed'

      runAfterActions(activeStep)
        .finally(() => {
          persistTutorialProgress(runtime, tutorial, event, activeStep, activeIndex, originalStepCount)
          currentDriver.destroy()
        })
    },
    onDestroyStarted: (_element, _step, { driver: currentDriver }) => {
      showDismissalReminder(currentDriver)
        .then((shown) => {
          if (shown) {
            return
          }

          finishDismissedTutorial(currentDriver)
        })
        .catch((error) => console.error(error))
    },
    onHighlighted: (element) => {
      normalizeDriverTargetAria(element)
    },
    onDestroyed: () => {
      activeDriver = null
      launcher?.focus()
    },
  })

  activeDriver.drive()
}

const runtimePayload = (runtime) => JSON.parse(runtime.dataset.payload ?? '{}')

const currentTutorial = (payload) => {
  const tutorials = payload.tutorials ?? []

  return tutorials[tutorials.length - 1]
}

const currentRuntime = () => document.querySelector(runtimeSelector)

const currentPayload = () => {
  const runtime = currentRuntime()

  return runtime ? runtimePayload(runtime) : {}
}

const currentTutorialIsAvailable = () => Boolean(currentTutorial(currentPayload()))

const startCurrentTutorial = async () => {
  if (startingTutorial) {
    return
  }

  const runtime = currentRuntime()
  const tutorial = currentTutorial(currentPayload())

  if (!runtime || !tutorial) {
    return
  }

  startingTutorial = true

  try {
    await startTutorial(runtime, tutorial)
  } finally {
    startingTutorial = false
  }
}

const listenForEvent = (target, eventName, listener) => {
  target.addEventListener(eventName, listener)

  return () => target.removeEventListener(eventName, listener)
}

const syncLauncherElements = () => {
  const available = currentTutorialIsAvailable()

  document.querySelectorAll(launcherSelector).forEach((launcher) => {
    launcher.hidden = !available
    launcher.dataset.filamentTutorialsBooted = 'true'

    if (launcher.dataset.filamentTutorialsClickBooted === 'true') {
      return
    }

    launcher.dataset.filamentTutorialsClickBooted = 'true'
    launcher.addEventListener('click', (event) => {
      event.preventDefault()
      event.stopImmediatePropagation()
      startCurrentTutorial().catch((error) => console.error(error))
    }, { capture: true })
  })

  return available
}

const dispatchLauncherAvailability = () => {
  const available = syncLauncherElements()

  document.dispatchEvent(new CustomEvent('filament-tutorials:launcher-availability-changed', {
    detail: {
      available,
    },
  }))
}

const bootFallbackLaunchers = () => {
  if (window.Alpine) {
    return
  }

  document.querySelectorAll(launcherSelector).forEach((launcher) => {
    launcher.hidden = !currentTutorialIsAvailable()
    launcher.dataset.filamentTutorialsBooted = 'true'

    if (launcher.dataset.filamentTutorialsFallbackBooted === 'true') {
      return
    }

    launcher.dataset.filamentTutorialsFallbackBooted = 'true'

    launcher.addEventListener('click', () => {
      startCurrentTutorial().catch((error) => console.error(error))
    })
  })
}

const initializeAlpineLaunchers = () => {
  if (!window.Alpine?.initTree) {
    return
  }

  document.querySelectorAll(launcherSelector).forEach((launcher) => {
    if (launcher._x_dataStack) {
      return
    }

    window.Alpine.initTree(launcher)
  })
}

const registerAlpineComponent = () => {
  if (alpineComponentRegistered || !window.Alpine) {
    return
  }

  alpineComponentRegistered = true

  window.Alpine.data('filamentTutorialsLauncher', () => ({
    available: false,
    listeners: [],

    init() {
      this.$el.dataset.filamentTutorialsBooted = 'true'
      this.available = currentTutorialIsAvailable()

      this.listeners.push(listenForEvent(
        document,
        'filament-tutorials:launcher-availability-changed',
        (event) => {
          this.available = Boolean(event.detail?.available)
        },
      ))
    },

    destroy() {
      this.listeners.forEach((removeListener) => removeListener())
      this.listeners = []
    },

    start() {
      startCurrentTutorial().catch((error) => console.error(error))
    },
  }))
}

const bootRuntime = (runtime) => {
  if (runtime.dataset.booted === 'true') {
    dispatchLauncherAvailability()

    return
  }

  runtime.dataset.booted = 'true'

  const payload = runtimePayload(runtime)

  registerAlpineComponent()
  initializeAlpineLaunchers()
  bootFallbackLaunchers()
  dispatchLauncherAvailability()

  if (currentTutorial(payload)?.autoStart) {
    startTutorial(runtime, currentTutorial(payload)).catch((error) => {
      console.error(error)
    })
  }
}

document.addEventListener('alpine:init', registerAlpineComponent)

const boot = () => {
  document.querySelectorAll(runtimeSelector).forEach(bootRuntime)
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot, { once: true })
} else {
  boot()
}

document.addEventListener('livewire:navigated', boot)

import { driver } from 'driver.js'
import '../css/filament-tutorials.css'

const runtimeSelector = '[data-filament-tutorials-runtime]'
const launcherSelector = '[data-filament-tutorials-launcher]'
let activeDriver = null
let alpineComponentRegistered = false

const destroyActiveDriver = () => {
  if (!activeDriver) {
    return
  }

  activeDriver.destroy()
  activeDriver = null
}

const visibleElement = (selector) => {
  const element = document.querySelector(selector)

  if (!element) {
    return null
  }

  const rect = element.getBoundingClientRect()

  if (rect.width <= 0 || rect.height <= 0) {
    return null
  }

  return element
}

const waitForElement = (selector, timeout = 2500) => {
  const existingElement = visibleElement(selector)

  if (existingElement) {
    return Promise.resolve(existingElement)
  }

  return new Promise((resolve, reject) => {
    const timeoutId = window.setTimeout(() => {
      observer.disconnect()
      reject(new Error(`Tutorial target not found: ${selector}`))
    }, timeout)

    const observer = new MutationObserver(() => {
      const element = visibleElement(selector)

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
    await clickSelector(parameters.selector ?? `[data-tour="${parameters.trigger}"]`)

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

  if (action.action === 'sidebar.open') {
    document.dispatchEvent(new CustomEvent('filament-tutorials:open-sidebar'))
  }
}

const runBeforeActions = async (step) => {
  for (const action of step.before ?? []) {
    await runAction(action)
  }
}

const waitForStepTarget = async (step) => {
  if (!step?.selector) {
    return
  }

  await waitForElement(step.selector)
}

const driverSteps = (tutorial) => {
  return (tutorial.steps ?? [])
    .filter((step) => step.selector)
    .map((step) => ({
      element: step.selector,
      popover: {
        title: step.title,
        description: step.description,
      },
    }))
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
  const labels = runtimePayload(runtime).labels
  const availableSteps = (tutorial.steps ?? []).filter((step) => step.selector)
  const steps = driverSteps(tutorial)

  if (steps.length === 0) {
    return
  }

  await runBeforeActions(availableSteps[0])
  await waitForStepTarget(availableSteps[0])

  activeDriver = driver({
    allowClose: true,
    animate: !window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    doneBtnText: labels.done,
    nextBtnText: labels.next,
    prevBtnText: labels.previous,
    progressText: labels.progress,
    showButtons: ['close', 'previous', 'next'],
    showProgress: true,
    steps,
    onNextClick: (_element, _step, { driver: currentDriver }) => {
      const activeIndex = currentDriver.getActiveIndex() ?? 0
      const nextStep = availableSteps[activeIndex + 1]

      if (!nextStep) {
        currentDriver.destroy()

        return
      }

      runBeforeActions(nextStep)
        .then(() => waitForStepTarget(nextStep))
        .then(() => currentDriver.moveNext())
        .then(() => window.requestAnimationFrame(() => normalizeDriverTargetAria()))
        .catch((error) => console.error(error))
    },
    onPrevClick: (_element, _step, { driver: currentDriver }) => {
      currentDriver.movePrevious()
      window.requestAnimationFrame(() => normalizeDriverTargetAria())
    },
    onCloseClick: (_element, _step, { driver: currentDriver }) => {
      currentDriver.destroy()
    },
    onDoneClick: (_element, _step, { driver: currentDriver }) => {
      currentDriver.destroy()
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
  const runtime = currentRuntime()
  const tutorial = currentTutorial(currentPayload())

  if (!runtime || !tutorial) {
    return
  }

  await startTutorial(runtime, tutorial)
}

const listenForEvent = (target, eventName, listener) => {
  target.addEventListener(eventName, listener)

  return () => target.removeEventListener(eventName, listener)
}

const dispatchLauncherAvailability = () => {
  document.dispatchEvent(new CustomEvent('filament-tutorials:launcher-availability-changed', {
    detail: {
      available: currentTutorialIsAvailable(),
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

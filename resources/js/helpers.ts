import {PostVersionStatus} from '@/types'

interface ImportMeta {
    env: {
        VITE_APP_NAME: string
        VITE_APP_URL: string
    }
}

export function getAppUrl(): string {
    return import.meta.env.VITE_APP_URL
}

export function changeTitle(title: string) {
    document.title = title + ' - ' + import.meta.env.VITE_APP_NAME
}

export function getHeaderHeight() {
    return remToPixels(getCssVariableValue('--header-height'))
}

export function getErrorMessageByCode(code: number) {
    switch (code) {
        case 403:
            return 'У вас нет доступа для совершения данной операции.'
        case 429:
            return 'Слишком много запросов, повторите позже.'
        case 500:
            return 'Произошла внутренняя ошибка...'
        default:
            return ''
    }
}

export function convertDateToString(date: Date) {
    return date.toISOString()
}

export function getFullDate(date: Date | string) {
    if (typeof date === 'string') {
        date = new Date(date)
    }
    return date.toLocaleString()
}

export function getRelativeDate(date: Date | string): string {
    const now = new Date()
    if (typeof date === 'string') {
        date = new Date(date)
    }
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000)

    if (diffInSeconds < 60) {
        return 'Только что'
    }

    const diffInMinutes = Math.floor(diffInSeconds / 60)
    if (diffInMinutes < 60) {
        return `${diffInMinutes} мин. назад`
    }

    const diffInHours = Math.floor(diffInMinutes / 60)
    if (diffInHours < 24) {
        return `${diffInHours} ч. назад`
    }

    const diffInDays = Math.floor(diffInHours / 24)
    if (diffInDays < 7) {
        return `${diffInDays} дн. назад`
    }

    const diffInWeeks = Math.floor(diffInDays / 7)
    if (diffInWeeks < 4) {
        return `${diffInWeeks} нед. назад`
    }

    const diffInMonths = Math.floor(diffInDays / 30)
    if (diffInMonths < 12) {
        return `${diffInMonths} мес. назад`
    }

    const diffInYears = Math.floor(diffInDays / 365)
    return `${diffInYears} г. назад`
}

export function getPostVersionStatusInfo(status: PostVersionStatus) {
    switch (status) {
        case PostVersionStatus.DRAFT:
            return {
                name: 'Черновик',
                severity: 'secondary'
            }
        case PostVersionStatus.PENDING:
            return {
                name: 'На рассмотрении',
                severity: 'warning'
            }
        case PostVersionStatus.ACCEPTED:
            return {
                name: 'Принято',
                severity: 'success'
            }
        case PostVersionStatus.REJECTED:
            return {
                name: 'Отклонено',
                severity: 'danger'
            }
    }
}

export function getCssVariableValue(variable: string) {
    return getComputedStyle(document.documentElement).getPropertyValue(variable).trim()
}

export function remToPixels(remValue: string) {
    const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize)
    return parseFloat(remValue) * rootFontSize
}

export function lockGlobalScroll() {
    document.body.classList.add('lock-scroll')
}

export function unlockGlobalScroll() {
    document.body.classList.remove('lock-scroll')
}

export function absolutePosition(
    element: HTMLElement,
    target: HTMLElement,
    useTargetContext: boolean = false,
    alignRight: boolean = false
) {
    const targetViewportInfo = target.getBoundingClientRect()
    const targetPosition = useTargetContext
        ? {top: target.offsetTop, left: target.offsetLeft}
        : {top: targetViewportInfo.top + window.scrollY, left: targetViewportInfo.left + window.scrollX}
    const viewportWidth = document.documentElement.clientWidth
    const viewportHeight = document.documentElement.clientHeight
    const headerHeight = getHeaderHeight()

    let top: number
    let left: number
    let transformOrigin: string

    const isYOverflow = targetViewportInfo.top + target.offsetHeight + element.offsetHeight > viewportHeight
    if (isYOverflow) {
        top = targetPosition.top - Math.min(element.offsetHeight, targetViewportInfo.top - headerHeight)
        transformOrigin = 'bottom'
    } else {
        top = targetPosition.top + target.offsetHeight
        transformOrigin = 'top'
    }

    const isXOverflow = targetViewportInfo.left + element.offsetWidth > viewportWidth
    if (isXOverflow || alignRight) {
        left = targetPosition.left + target.offsetWidth - element.offsetWidth
        const scrollX = targetPosition.left - targetViewportInfo.left
        if (left < scrollX) {
            left = scrollX
        }
    } else {
        left = targetPosition.left
    }

    if (element.offsetWidth < target.offsetWidth) {
        element.style.width = target.offsetWidth + 'px'
    }
    element.style.maxWidth = viewportWidth + 'px'
    element.style.maxHeight = viewportHeight - headerHeight + 'px'

    element.style.top = top + 'px'
    element.style.left = left + 'px'
    element.style.transformOrigin = transformOrigin
}

export function isElementInOverflowedContainer(element: HTMLElement) {
    let currentElement = element.parentElement

    while (currentElement && currentElement !== document.body) {
        const overflowX = window.getComputedStyle(currentElement).overflowX;
        const overflowY = window.getComputedStyle(currentElement).overflowY;

        if ((overflowX !== 'visible' && currentElement.scrollWidth > currentElement.clientWidth) ||
            (overflowY !== 'visible' && currentElement.scrollHeight > currentElement.clientHeight)
        ) {
            return true;
        }

        currentElement = currentElement.parentElement;
    }

    return false
}


import {MaterialSubmissionStatus} from '@/types'
import {useGlobalModalStore} from '@/stores/global-modal'
import Cookies from 'js-cookie'
import {useAuthStore} from '@/stores/auth'
import axios, {type AxiosError} from 'axios'
import {useToastStore} from '@/stores/toast'

interface ImportMeta {
    env: {
        VITE_APP_NAME: string
        VITE_APP_URL: string
        VITE_HCAPTCHA_SITEKEY: string
        VITE_HCAPTCHA_ENABLED: string
    }
}

export function getAppUrl(): string {
    return import.meta.env.VITE_APP_URL
}

export function getHCaptchaSiteKey(): string {
    return import.meta.env.VITE_HCAPTCHA_SITEKEY
}

export function getTitle() {
    return document.title.slice(0, -(' — ' + import.meta.env.VITE_APP_NAME).length)
}

export function changeTitle(title: string) {
    document.title = title + ' — ' + import.meta.env.VITE_APP_NAME
}

export function countHTMLTag(html: string | undefined, tag: string) {
    if (!html) return
    return (html.match(new RegExp(tag, 'g')) || []).length
}

export function getHeaderHeight() {
    return convertToPixels(getCssVariableValue('--header-height'))
}

export function getErrorMessageByCode(code: number) {
    switch (code) {
        case 403:
            return 'У вас нет доступа для совершения данной операции.'
        case 404:
            return 'Ресурс не найден.'
        case 429:
            return 'Слишком много запросов, повторите позже.'
        case 500:
            return 'Произошла внутренняя ошибка...'
        default:
            return ''
    }
}

export function withCaptcha(action: () => void) {
    if (
        import.meta.env.VITE_HCAPTCHA_ENABLED !== 'false'
        && !useAuthStore().isModerator
        && !Cookies.get('captcha_solved')
    ) {
        useGlobalModalStore().captchaModal = true
        useGlobalModalStore().captchaModalAction = async (captchaToken: string) => {
            axios.post('/api/captcha-tokens', {token: captchaToken}).then((response) => {
                if (response.data.success) {
                    const expiry = new Date(response.data.expiry)
                    Cookies.set('captcha_solved', '1', {expires: expiry})
                    action()
                } else {
                    if (response.data.message) {
                        useToastStore().error(`${response.data.message}`)
                    }
                    return
                }
            }).catch((error: AxiosError) => {
                useToastStore().error(getErrorMessageByCode(error.response!.status))
            })
        }
    } else {
        action()
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

export function getCounterLabel(counter: number) {
    if (counter > 999) {
        if (counter > 999999) {
            return ((counter / 1000000).toFixed(2)).toLocaleString() + 'M'
        } else {
            return ((counter / 1000).toFixed(1)).toLocaleString() + 'K'
        }
    } else {
        return counter
    }
}

export function getFullPresentableDate(date: Date | string) {
    if (typeof date === 'string') {
        date = new Date(date)
        let hours = date.getHours() < 10 ? '0' + date.getHours() : date.getHours()
        let minutes = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()
        return date.getDate() + ' ' +
            getMonthName(date.getMonth()) +
            getCheckedYear(date.getFullYear()) + ', ' +
            hours + ':' + minutes
    } else {
        return ''
    }
}

export function getRelativeDate(date: Date | string, lowercase: boolean = false): string {
    const now = new Date()
    if (typeof date === 'string') {
        date = new Date(date)
    }
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000)

    if (diffInSeconds < 60) {
        const result = 'Только что'
        return lowercase ? result.toLowerCase() : result
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
        return `${Math.max(1, diffInWeeks)} нед. назад`
    }

    const diffInMonths = Math.floor(diffInDays / 30)
    if (diffInMonths < 12) {
        return `${Math.max(1, diffInMonths)} мес. назад`
    }

    const diffInYears = Math.floor(diffInDays / 365)
    return `${Math.max(1, diffInYears)} г. назад`
}

export function getMonthName(number: number) {
    switch (number) {
        case 0:
            return 'января'
        case 1:
            return 'февраля'
        case 2:
            return 'марта'
        case 3:
            return 'апреля'
        case 4:
            return 'мая'
        case 5:
            return 'июня'
        case 6:
            return 'июля'
        case 7:
            return 'августа'
        case 8:
            return 'сентября'
        case 9:
            return 'октября'
        case 10:
            return 'ноября'
        case 11:
            return 'декабря'
    }
}

export function getCheckedYear(number: number) {
    const now = new Date()
    if (number !== now.getFullYear()) {
        return ','
    } else {
        return ' ' + number
    }
}

export function getMaterialSubmissionStatusInfo(status: MaterialSubmissionStatus) {
    switch (status) {
        case MaterialSubmissionStatus.ACCEPTED:
            return {
                colorClass: 'success',
                name: 'Принято'
            }
        case MaterialSubmissionStatus.DRAFT:
            return {
                colorClass: 'draft',
                name: 'Черновик',
            }
        case MaterialSubmissionStatus.PENDING:
            return {
                colorClass: 'pending',
                name: 'На рассмотрении'
            }
        case MaterialSubmissionStatus.REJECTED:
            return {
                colorClass: 'danger',
                name: 'Отклонено'
            }
    }
}

export function getCssVariableValue(variable: string) {
    return getComputedStyle(document.documentElement).getPropertyValue(variable).trim()
}

export function convertToPixels(value: string) {
    const tempElement = document.createElement('div')

    document.body.appendChild(tempElement)

    tempElement.style.width = value

    const pixels = window.getComputedStyle(tempElement).width

    document.body.removeChild(tempElement)

    return parseFloat(pixels)
}

export function lockGlobalScroll() {
    document.body.classList.add('lock-scroll')

    // Используемый 'scrollbar-gutter: stable' в Chromium не добавляет отступа для fixed элементов,
    // когда содержимое не прокручивается, поэтому нам приходится добавлять отступ самостоятельно.
    // Решение достаточно костыльное, и fixed элементы все равно дергаются при изменении высоты окна
    // (когда высота страницы становится меньше высоты окна).
    //
    // В будущем убрать это, когда баг с scrollbar-gutter исправится в Chromium
    // (https://issues.chromium.org/issues/40792788).
    if (window.innerHeight <= document.documentElement.offsetHeight) {
        const currentScrollbarWidth = window.innerWidth - document.documentElement.offsetWidth
        document.body.style.setProperty('--current-global-scrollbar-width', currentScrollbarWidth + 'px')
    }
}

export function unlockGlobalScroll() {
    document.body.classList.remove('lock-scroll')
    document.body.style.removeProperty('--current-global-scrollbar-width')
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
        const overflowX = window.getComputedStyle(currentElement).overflowX
        const overflowY = window.getComputedStyle(currentElement).overflowY

        if ((overflowX !== 'visible' && currentElement.scrollWidth > currentElement.clientWidth) ||
            (overflowY !== 'visible' && currentElement.scrollHeight > currentElement.clientHeight)
        ) {
            return true
        }

        currentElement = currentElement.parentElement
    }

    return false
}

import {reactive} from 'vue'
import {GameEdition} from '@/types'

class PreferenceManager {
    private static readonly defaultEdition = GameEdition.BEDROCK
    private static readonly themeLinkElementId = 'theme-link'
    private static readonly themeKey = 'theme'
    private static readonly isDesktopSidebarKey = 'is-desktop-sidebar'
    private static readonly isHeaderFixedKey = 'is-header-fixed'
    private static readonly editionKey = 'edition'

    private theme = 'ld-cyan-light'
    private isDesktopSidebar = false
    private isHeaderFixed = false
    private edition = PreferenceManager.defaultEdition

    public isLightTheme() {
        return this.theme.endsWith('light')
    }

    public switchLightTheme() {
        this.theme = this.theme.replace('light', 'dark')

        // this.changeTheme(this.theme)
        localStorage.setItem(PreferenceManager.themeKey, this.theme)
    }

    private changeTheme(newTheme: string) {
        if (this.theme !== newTheme) {
            const linkElement = document.getElementById(PreferenceManager.themeLinkElementId)

            if (linkElement === null) {
                return
            }

            const newThemeUrl = linkElement.getAttribute('href')!.replace(this.theme, newTheme)

            const cloneLinkElement = document.createElement('link')
            cloneLinkElement.setAttribute('id', PreferenceManager.themeLinkElementId + '-clone')
            cloneLinkElement.setAttribute('rel', 'stylesheet')
            cloneLinkElement.setAttribute('href', newThemeUrl)
            cloneLinkElement.addEventListener('load', () => {
                linkElement?.remove()
                cloneLinkElement.setAttribute('id', PreferenceManager.themeLinkElementId)
                this.theme = newTheme
            })
            linkElement.parentNode?.insertBefore(cloneLinkElement, linkElement.nextSibling)
        }
    }

    public loadTheme() {
        const theme = localStorage.getItem(PreferenceManager.themeKey)

        if (theme != null && theme !== this.theme) {
            this.changeTheme(theme)
        }
    }

    public isDesktopSidebarVisible() {
        return this.isDesktopSidebar
    }

    public switchDesktopSidebar() {
        this.isDesktopSidebar = !this.isDesktopSidebar
        localStorage.setItem(PreferenceManager.isDesktopSidebarKey, String(this.isDesktopSidebar))
    }

    public loadDesktopSidebar() {
        this.isDesktopSidebar = localStorage.getItem(PreferenceManager.isDesktopSidebarKey) === 'true'
    }

    public isHeaderFixedVisible() {
        return this.isHeaderFixed
    }

    public switchHeaderFixed() {
        this.isHeaderFixed = !this.isHeaderFixed
        localStorage.setItem(PreferenceManager.isHeaderFixedKey, String(this.isHeaderFixed))
    }

    public loadHeaderFixed() {
        this.isHeaderFixed = localStorage.getItem(PreferenceManager.isHeaderFixedKey) === 'true'
    }

    public getEdition() {
        return this.edition
    }

    public setEdition(edition: GameEdition) {
        this.edition = edition
        localStorage.setItem(PreferenceManager.editionKey, edition)
    }

    public loadEdition() {
        const storageValue = localStorage.getItem(PreferenceManager.editionKey) as GameEdition.BEDROCK | null
        this.edition = storageValue || PreferenceManager.defaultEdition
    }

    public load() {
        this.loadTheme()
        this.loadDesktopSidebar()
        this.loadHeaderFixed()
        this.loadEdition()
    }
}

const preferenceManager = reactive(new PreferenceManager())

export default function usePreferenceManager() {
    return preferenceManager
}

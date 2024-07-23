import {reactive, ref} from 'vue'

class DesignManager {
    private static readonly lightTheme = 'light'
    private static readonly darkTheme = 'dark'
    private static readonly themeLinkElementId = 'theme-link'
    private static readonly themeKey = 'theme'
    private static readonly isDesktopSidebarKey = 'is-desktop-sidebar'
    private static readonly isHeaderFixedKey = 'is-header-fixed'

    private theme = DesignManager.lightTheme

    private isDesktopSidebar = false
    private isHeaderFixed = false

    public isDesktopSidebarVisible() {
        return this.isDesktopSidebar
    }

    public isHeaderFixedVisible() {
        return this.isHeaderFixed
    }

    public switchDesktopSidebar() {
        this.isDesktopSidebar = !this.isDesktopSidebar
        localStorage.setItem(DesignManager.isDesktopSidebarKey, this.isDesktopSidebar)
    }

    public switchHeaderFixed() {
        this.isHeaderFixed = !this.isHeaderFixed
        localStorage.setItem(DesignManager.isHeaderFixedKey, this.isHeaderFixed)
    }

    public loadDesktopSidebar() {
        this.isDesktopSidebar = localStorage.getItem(DesignManager.isDesktopSidebarKey) === 'true'
    }

    public loadHeaderFixed() {
        this.isHeaderFixed = localStorage.getItem(DesignManager.isHeaderFixedKey) === 'true'
    }

























    public loadTheme() {
        const theme = localStorage.getItem(DesignManager.themeKey)

        if (theme != null && theme !== this.currentTheme) {
            this.changeTheme(theme)
        }
    }

    public toggleTheme() {
        const newTheme = this.currentTheme === DesignManager.lightTheme ? DesignManager.darkTheme : DesignManager.lightTheme

        this.changeTheme(newTheme)
        localStorage.setItem(DesignManager.themeKey, newTheme)
    }

    private changeTheme(newTheme: string) {
        if (this.currentTheme !== newTheme) {
            const linkElement = document.getElementById(DesignManager.themeLinkElementId)

            if (linkElement === null) { return }

            const newThemeUrl = linkElement.getAttribute('href')!.replace(this.currentTheme, newTheme)

            const cloneLinkElement = document.createElement('link')
            cloneLinkElement.setAttribute('id', DesignManager.themeLinkElementId + '-clone')
            cloneLinkElement.setAttribute('rel', 'stylesheet')
            cloneLinkElement.setAttribute('href', newThemeUrl)
            cloneLinkElement.addEventListener('load', () => {
                linkElement?.remove()
                cloneLinkElement.setAttribute('id', DesignManager.themeLinkElementId);
                this.currentTheme = newTheme
            })
            linkElement.parentNode?.insertBefore(cloneLinkElement, linkElement.nextSibling)
        }
    }

    public isLight() {
        return this.currentTheme === DesignManager.lightTheme
    }

    public load()
    {
        this.loadDesktopSidebar()
        this.loadHeaderFixed()
        this.loadTheme()
    }
}

const designManager = reactive(new DesignManager())

export default function useDesignManager() {
    return designManager
}

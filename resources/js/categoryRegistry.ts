import {reactive} from 'vue'
import {CategoryType, GameEdition} from '@/types'

export interface Category {
    type: CategoryType
    slug: string
    name: string
    icon: string
    edition: GameEdition | null
    isDownloadable: boolean
}

class CategoryRegistry {
    private static readonly categories: { [type: string]: Category } = {
        [CategoryType.BEDROCK_RESOURCE_PACKS]: {
            type: CategoryType.BEDROCK_RESOURCE_PACKS,
            slug: 'resource-packs',
            name: 'Ресурс-Паки',
            icon: 'icon-axolotl-bucket',
            edition: GameEdition.BEDROCK,
            isDownloadable: true,
        },
        [CategoryType.BEDROCK_ADDONS]: {
            type: CategoryType.BEDROCK_ADDONS,
            slug: 'addons',
            name: 'Аддоны',
            icon: 'icon-spawn-egg',
            edition: GameEdition.BEDROCK,
            isDownloadable: true,
        },
        [CategoryType.BEDROCK_MAPS]: {
            type: CategoryType.BEDROCK_MAPS,
            slug: 'maps',
            name: 'Карты',
            icon: 'icon-map',
            edition: GameEdition.BEDROCK,
            isDownloadable: true,
        },
        [CategoryType.JAVA_RESOURCE_PACKS]: {
            type: CategoryType.JAVA_RESOURCE_PACKS,
            slug: 'resource-packs',
            name: 'Ресурс-Паки',
            icon: 'icon-axolotl-bucket',
            edition: GameEdition.JAVA,
            isDownloadable: true,
        },
        [CategoryType.JAVA_DATA_PACKS]: {
            type: CategoryType.JAVA_DATA_PACKS,
            slug: 'data-packs',
            name: 'Дата-Паки',
            icon: 'icon-spawn-egg',
            edition: GameEdition.JAVA,
            isDownloadable: true,
        },
        [CategoryType.JAVA_MODS]: {
            type: CategoryType.JAVA_MODS,
            slug: 'mods',
            name: 'Моды',
            icon: 'icon-diamond',
            edition: GameEdition.JAVA,
            isDownloadable: true,
        },
        [CategoryType.JAVA_MAPS]: {
            type: CategoryType.JAVA_MAPS,
            slug: 'maps',
            name: 'Карты',
            icon: 'icon-map',
            edition: GameEdition.JAVA,
            isDownloadable: true,
        },
        [CategoryType.SKINS]: {
            type: CategoryType.SKINS,
            slug: 'skins',
            name: 'Скины',
            icon: 'icon-skin',
            edition: null,
            isDownloadable: true,
        },
        [CategoryType.ARTICLES]: {
            type: CategoryType.ARTICLES,
            slug: 'articles',
            name: 'Статьи',
            icon: 'icon-news',
            edition: null,
            isDownloadable: false,
        }
    }

    public getAll() {
        return Object.values(CategoryRegistry.categories)
    }

    public getByEdition(edition: GameEdition | null) {
        return this.getAll().filter((category) => category.edition === edition || category.edition === null)
    }

    public get(type: CategoryType) {
        return CategoryRegistry.categories[type]
    }

    public getBySlugAndEdition(slug: string, edition: GameEdition | null) {
        return this.getAll().find(
            (category) => category.slug === slug && (category.edition === edition || category.edition === null)
        )
    }
}

const categoryRegistry = reactive(new CategoryRegistry())

export default function useCategoryRegistry() {
    return categoryRegistry
}

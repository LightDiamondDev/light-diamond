export enum UserRole {
    USER = 'USER',
    MODERATOR = 'MODERATOR',
    ADMIN = 'ADMIN',
}

export interface User {
    id?: bigint
    username?: string
    email?: string
    email_verified_at?: string | null
    first_name?: string
    last_name?: string
    role?: UserRole
    post_count?: number
    favourite_post_count?: number
    comment_count?: number
    collected_like_count?: number
    collected_download_count?: number
    collected_view_count?: number
    created_at?: string
    updated_at?: string
}

export enum GameEdition {
    BEDROCK = 'BEDROCK',
    JAVA = 'JAVA'
}

export enum CategoryType {
    ARTICLES = 'ARTICLES',
    SKINS = 'SKINS',
    BEDROCK_RESOURCE_PACKS = 'BEDROCK_RESOURCE_PACKS',
    BEDROCK_ADDONS = 'BEDROCK_ADDONS',
    BEDROCK_MAPS = 'BEDROCK_MAPS',
    JAVA_RESOURCE_PACKS = 'JAVA_RESOURCE_PACKS',
    JAVA_DATA_PACKS = 'JAVA_DATA_PACKS',
    JAVA_MODS = 'JAVA_MODS',
    JAVA_MAPS = 'JAVA_MAPS'
}

export interface Post {
    id: bigint
    slug: string
    version?: PostVersion
    like_count: number
    favourite_count: number
    comment_count: number
    view_count: number
    download_count: number
    is_liked: boolean
    is_favourite: boolean
    created_at: string
    updated_at: string
}

export interface PostComment {
    id: bigint
    parent_comment_id?: bigint | null
    parent_comment?: PostComment | null
    post_id: bigint
    post?: Post
    user_id: bigint | null
    user?: User | null
    content: string
    like_count: number
    is_liked: boolean
    created_at: string
    updated_at: string
}

export interface PostVersionFile {
    id?: bigint
    post_version_id?: bigint
    name: string
    path?: string | null
    url?: string | null
    size?: number | null
    extension?: string | null
}

export enum PostVersionStatus {
    DRAFT = 'DRAFT',
    PENDING = 'PENDING',
    ACCEPTED = 'ACCEPTED',
    REJECTED = 'REJECTED',
}

export interface PostVersion {
    id?: bigint
    post_id?: bigint | null
    post?: Post | null
    author_id?: bigint | null
    author?: User | null
    assigned_moderator_id?: bigint | null
    assigned_moderator?: User | null
    category?: CategoryType
    cover?: string
    cover_url?: string
    cover_file?: File
    title?: string
    description?: string
    content?: string
    status?: PostVersionStatus
    actions?: PostVersionAction[]
    files?: PostVersionFile[]
    created_at?: string
    updated_at?: string
}

export enum PostVersionActionType {
    SUBMIT = 'SUBMIT',
    REQUEST_CHANGES = 'REQUEST_CHANGES',
    ACCEPT = 'ACCEPT',
    REJECT = 'REJECT',
    ASSIGN_MODERATOR = 'ASSIGN_MODERATOR',
}

export interface PostVersionActionRequestChanges {
    message: string
}

export interface PostVersionActionReject {
    reason: string
}

export interface PostVersionActionAssignModerator {
    moderator_id: bigint
    moderator: User | null
}

export interface PostVersionAction {
    id?: bigint
    version_id?: bigint
    version?: PostVersion
    user_id?: bigint | null
    user?: User | null
    type?: PostVersionActionType
    details?: {} | PostVersionActionRequestChanges | PostVersionActionReject | PostVersionActionAssignModerator
    created_at?: string
    updated_at?: string
}

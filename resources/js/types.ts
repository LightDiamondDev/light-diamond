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
    materials_count?: number
    favourite_materials_count?: number
    comments_count?: number
    collected_likes_count?: number
    collected_downloads_count?: number
    collected_views_count?: number
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

export interface Material {
    id?: bigint
    slug?: string
    category?: CategoryType
    edition?: GameEdition | null
    state?: MaterialState | null
    version?: MaterialVersion | null
    versions?: MaterialVersion[]
    likes_count?: number
    favourites_count?: number
    comments_count?: number
    views_count?: number
    downloads_count?: number
    is_liked?: boolean
    is_favourite?: boolean
    active_submission_id?: bigint
    published_at?: string | null
    deleted_at?: string | null
    created_at?: string
    updated_at?: string
}

export interface MaterialState {
    id?: bigint
    material_id?: bigint
    author_id?: bigint | null
    localization?: MaterialLocalization
    localizations?: MaterialLocalization[]
    author?: User
    published_at?: string
    created_at?: string
    updated_at?: string
}

export interface MaterialLocalization {
    id?: bigint
    material_state_id?: bigint
    language?: string
    cover?: string
    cover_url?: string
    title?: string
    description?: string
    content?: string
}

export enum MaterialSubmissionStatus {
    DRAFT = 'DRAFT',
    PENDING = 'PENDING',
    ACCEPTED = 'ACCEPTED',
    REJECTED = 'REJECTED',
}

export enum SubmissionType {
    CREATE = 'CREATE',
    UPDATE = 'UPDATE',
    DELETE = 'DELETE',
}

export interface MaterialSubmission {
    id?: bigint
    material_id?: bigint | null
    material?: Material | null
    material_state?: MaterialState | null
    submitter_id?: bigint | null
    submitter?: User | null
    assigned_moderator_id?: bigint | null
    assigned_moderator?: User | null
    type?: SubmissionType
    status?: MaterialSubmissionStatus
    actions?: MaterialSubmissionAction[]
    version_submissions?: MaterialVersionSubmission[]
    created_at?: string
    updated_at?: string
}

export enum MaterialSubmissionActionType {
    SUBMIT = 'SUBMIT',
    REQUEST_CHANGES = 'REQUEST_CHANGES',
    ACCEPT = 'ACCEPT',
    REJECT = 'REJECT',
    ASSIGN_MODERATOR = 'ASSIGN_MODERATOR',
    MESSAGE = 'MESSAGE',
}

export interface MaterialSubmissionActionRequestChanges {
    message?: string
}

export interface MaterialSubmissionActionReject {
    reason?: string
}

export interface MaterialSubmissionActionAssignModerator {
    moderator_id?: bigint
    moderator?: User | null
}

export interface MaterialSubmissionActionMessage {
    message?: string
    is_moderator?: boolean
}

export interface MaterialSubmissionAction {
    id?: bigint
    submission_id?: bigint
    user_id?: bigint | null
    user?: User | null
    type?: MaterialSubmissionActionType
    details?: {} | MaterialSubmissionActionRequestChanges | MaterialSubmissionActionReject | MaterialSubmissionActionAssignModerator | MaterialSubmissionActionMessage
    created_at?: string
    updated_at?: string
}

export interface MaterialVersion {
    id?: bigint
    material?: Material | null
    material_id?: bigint
    state?: MaterialVersionState | null
    files?: MaterialFile[] | null
    published_at?: string
    deleted_at?: string | null
    created_at?: string
    updated_at?: string
}

export interface MaterialVersionState {
    id?: bigint
    version_id?: bigint
    number?: string
    localization?: MaterialVersionLocalization | null
    localizations?: MaterialVersionLocalization[]
    published_at?: string
    created_at?: string
    updated_at?: string
}

export interface MaterialVersionLocalization {
    id?: bigint
    version_state_id?: bigint
    language?: string
    name?: string | null
    changelog?: string | null
    created_at?: string
    updated_at?: string
}

export interface MaterialVersionSubmission {
    id?: bigint
    version_id?: bigint
    version?: MaterialVersion
    material_submission_id?: bigint
    version_state_id?: bigint | null
    version_state?: MaterialVersionState | null
    type?: SubmissionType
    file_submissions?: MaterialFileSubmission[]
}

export interface MaterialFile {
    id?: bigint
    version_id?: bigint
    state?: MaterialFileState | null
    path?: string | null
    url?: string | null
    size?: number | null
    extension?: string | null
    published_at?: string
    deleted_at?: string | null
    created_at?: string
    updated_at?: string
}

export interface MaterialFileState {
    id?: bigint
    file_id?: bigint
    localization?: MaterialFileLocalization | null
    localizations?: MaterialFileLocalization[]
    published_at?: string
    created_at?: string
    updated_at?: string
}

export interface MaterialFileLocalization {
    id?: bigint
    file_state_id?: bigint
    language?: string
    name?: string
}

export interface MaterialFileSubmission {
    id?: bigint
    file_id?: bigint
    file?: MaterialFile | null
    version_submission_id?: bigint
    file_state_id?: bigint | null
    file_state?: MaterialFileState | null
    type?: SubmissionType
}

export interface MaterialComment {
    id: bigint
    parent_comment_id?: bigint | null
    parent_comment?: MaterialComment | null
    material_id: bigint
    version?: MaterialVersion
    version_id?: bigint
    user_id: bigint | null
    user?: User | null
    content: string
    likes_count: number
    is_liked: boolean
    created_at: string
    updated_at: string
}

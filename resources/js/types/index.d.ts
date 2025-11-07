import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

const Language = {
    Php: 'php',
    JavaScript: 'javascript',
    TypeScript: 'typescript',
    Python: 'python',
    Java: 'java',
    CSharp: 'csharp',
    Cpp: 'cpp',
    C: 'c',
    Go: 'go',
    Rust: 'rust',
    Ruby: 'ruby',
    Swift: 'swift',
    Kotlin: 'kotlin',
    Html: 'html',
    Css: 'css',
    Sql: 'sql',
    Bash: 'bash',
    Json: 'json',
    Xml: 'xml',
    Markdown: 'markdown',
    Yaml: 'yaml',
    Text: 'text',
} as const;

export type Language = (typeof Language)[keyof typeof Language];

const MimeType = {
    Png: 'image/png',
    Jpg: 'image/jpeg',
    Gif: 'image/gif',
    Mp4: 'video/mp4',
} as const;

export type MimeType = (typeof MimeType)[keyof typeof MimeType];

export interface FragCode {
    // columns
    id: number;
    user_id: number;
    title: string | null;
    code: string;
    language: Language;
    created_at: string | null;
    updated_at: string | null;
    // relations
    user?: User;
    // counts
    // exists
    user_exists?: boolean;
}

export interface FragFile {
    // columns
    id: number;
    user_id: number;
    filename: string;
    path: string;
    mime_type: MimeType;
    size: number;
    created_at: string | null;
    updated_at: string | null;
    // relations
    user?: User;
    // counts
    // exists
    user_exists?: boolean;
}

export type BreadcrumbItemType = BreadcrumbItem;

<?php

declare(strict_types=1);

namespace App;

enum Language: string
{
    case Php = 'php';
    case JavaScript = 'javascript';
    case TypeScript = 'typescript';
    case Python = 'python';
    case Java = 'java';
    case CSharp = 'csharp';
    case Cpp = 'cpp';
    case C = 'c';
    case Go = 'go';
    case Rust = 'rust';
    case Ruby = 'ruby';
    case Swift = 'swift';
    case Kotlin = 'kotlin';
    case Html = 'html';
    case Css = 'css';
    case Sql = 'sql';
    case Bash = 'bash';
    case Json = 'json';
    case Xml = 'xml';
    case Markdown = 'markdown';
    case Yaml = 'yaml';
    case Text = 'text';

    public function displayName(): string
    {
        return match ($this) {
            self::Php => 'PHP',
            self::JavaScript => 'JavaScript',
            self::TypeScript => 'TypeScript',
            self::Python => 'Python',
            self::Java => 'Java',
            self::CSharp => 'C#',
            self::Cpp => 'C++',
            self::C => 'C',
            self::Go => 'Go',
            self::Rust => 'Rust',
            self::Ruby => 'Ruby',
            self::Swift => 'Swift',
            self::Kotlin => 'Kotlin',
            self::Html => 'HTML',
            self::Css => 'CSS',
            self::Sql => 'SQL',
            self::Bash => 'Bash',
            self::Json => 'JSON',
            self::Xml => 'XML',
            self::Markdown => 'Markdown',
            self::Yaml => 'YAML',
            self::Text => 'Plain Text',
        };
    }
}

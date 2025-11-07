<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Language;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FragCode>
 */
class FragCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $language = fake()->randomElement(Language::cases());

        $codeExamples = [
            Language::Php => '<?php\n\nfunction hello() {\n    return "Hello World";\n}',
            Language::JavaScript => 'function hello() {\n    return "Hello World";\n}',
            Language::Python => 'def hello():\n    return "Hello World"',
            Language::Html => '<div>\n    <h1>Hello World</h1>\n</div>',
            Language::Css => '.container {\n    display: flex;\n    color: blue;\n}',
        ];

        $code = $codeExamples[$language] ?? fake()->paragraphs(3, true);

        return [
            'user_id' => User::factory(),
            'title' => fake()->optional()->sentence(),
            'code' => $code,
            'language' => $language,
        ];
    }
}

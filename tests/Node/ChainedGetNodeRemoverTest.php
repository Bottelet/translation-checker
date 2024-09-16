<?php

namespace Bottelet\TranslationChecker\Tests\Node;

use Bottelet\TranslationChecker\Extractor\BladeFileExtractor;
use Bottelet\TranslationChecker\Extractor\PhpClassExtractor;
use Bottelet\TranslationChecker\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ChainedGetNodeRemoverTest extends TestCase
{
    #[Test]
    public function canHandleChainedGetCall(): void
    {
        $code = <<<'CODE'
           {{ __('Address') }} *
                        @foreach(\App\Services\Service::orderBy('display_name')->get() as $city) 
                      
                        @endforeach
{{ __('Text after chained get') }} *<
         
CODE;

        $file = $this->createTempFile('edit.blade.php', $code);

        $bladeExtractor = new BladeFileExtractor;
        $foundStrings = $bladeExtractor->extractFromFile($file);
        $this->assertContains('Address', $foundStrings);
        $this->assertContains('Text after chained get', $foundStrings);
    }

    #[Test]
    public function canHandleGetCall(): void
    {
        $code = <<<'CODE'
{{ __('Test') }} *
                       {{\App\Services\Service::orderBy('display_name')->get() }} 
             
{{ __('Text after chained get') }} *
         
CODE;

        $file = $this->createTempFile('edit.blade.php', $code);

        $bladeExtractor = new BladeFileExtractor;
        $foundStrings = $bladeExtractor->extractFromFile($file);
        $this->assertContains('Test', $foundStrings);
        $this->assertContains('Text after chained get', $foundStrings);
    }
    #[Test]
    public function canHandleNestedGetCalls(): void
    {
        $code = <<<'CODE'
{{ __('Start') }}
@foreach(\App\Models\User::where('active', true)->get() as $user)
    {{ $user->posts()->where('published', true)->get()->count() }}
@endforeach
{{ __('End') }}
CODE;

        $file = $this->createTempFile('nested.blade.php', $code);

        $bladeExtractor = new BladeFileExtractor;
        $foundStrings = $bladeExtractor->extractFromFile($file);
        $this->assertContains('Start', $foundStrings);
        $this->assertContains('End', $foundStrings);
    }

    #[Test]
    public function canHandleMultipleGetCallsInSameLine(): void
    {
        $code = <<<'CODE'
{{ __('Multiple gets') }}
{{ \App\Models\Post::latest()->get()->merge(\App\Models\Page::get()) }}
{{ __('After multiple gets') }}
CODE;

        $file = $this->createTempFile('multiple_gets.blade.php', $code);

        $bladeExtractor = new BladeFileExtractor;
        $foundStrings = $bladeExtractor->extractFromFile($file);
        $this->assertContains('Multiple gets', $foundStrings);
        $this->assertContains('After multiple gets', $foundStrings);
    }

    #[Test]
    public function canHandleGetWithArguments(): void
    {
        $code = <<<'CODE'
{{ __('Before get with args') }}
{{ \App\Models\Post::get(['title', 'content']) }}
{{ __('After get with args') }}
CODE;

        $file = $this->createTempFile('get_with_args.blade.php', $code);

        $bladeExtractor = new BladeFileExtractor;
        $foundStrings = $bladeExtractor->extractFromFile($file);
        $this->assertContains('Before get with args', $foundStrings);
        $this->assertContains('After get with args', $foundStrings);
    }

    #[Test]
    public function canHandleGetInComplexExpression(): void
    {
        $code = <<<'CODE'
{{ __('Complex expression') }}
@if(count(\App\Models\User::where('role', 'admin')->get()) > 5)
    {{ __('Many admins') }}
@else
    {{ __('Few admins') }}
@endif
CODE;

        $file = $this->createTempFile('complex_expression.blade.php', $code);

        $bladeExtractor = new BladeFileExtractor;
        $foundStrings = $bladeExtractor->extractFromFile($file);
        $this->assertContains('Complex expression', $foundStrings);
        $this->assertContains('Many admins', $foundStrings);
        $this->assertContains('Few admins', $foundStrings);
    }

    #[Test]
    public function canHandleGetInPhpBlock(): void
    {
        $code = <<<'CODE'
{{ __('PHP block start') }}
@php
$users = \App\Models\User::all()->filter(function($user) {
    return $user->posts()->get()->isNotEmpty();
});
@endphp
{{ __('PHP block end') }}
CODE;

        $file = $this->createTempFile('php_block.blade.php', $code);

        $bladeExtractor = new BladeFileExtractor;
        $foundStrings = $bladeExtractor->extractFromFile($file);
        $this->assertContains('PHP block start', $foundStrings);
        $this->assertContains('PHP block end', $foundStrings);
    }
    #[Test]
    public function canHandleGetWithinForeach(): void
    {
        $code = <<<'CODE'
{{ __('Start of foreach') }}
@foreach(\App\Models\Category::get() as $category)
    {{ __('Category: :name', ['name' => $category->name]) }}
@endforeach
{{ __('End of foreach') }}
CODE;

        $file = $this->createTempFile('get_within_foreach.blade.php', $code);

        $bladeExtractor = new BladeFileExtractor;
        $foundStrings = $bladeExtractor->extractFromFile($file);
        $this->assertContains('Start of foreach', $foundStrings);
        $this->assertContains('Category: :name', $foundStrings);
        $this->assertContains('End of foreach', $foundStrings);
    }

    #[Test]
    public function canHandleGetWithinNestedStructures(): void
    {
        $code = <<<'CODE'
{{ __('Start of nested structure') }}
@foreach(\App\Models\Department::get() as $department)
    <h2>{{ __('Department: :name', ['name' => $department->name]) }}</h2>
    <ul>
    @foreach($department->employees()->get() as $employee)
        <li>{{ __('Employee: :name', ['name' => $employee->name]) }}</li>
    @endforeach
    </ul>
@endforeach
{{ __('End of nested structure') }}
CODE;

        $file = $this->createTempFile('get_within_nested_structures.blade.php', $code);

        $bladeExtractor = new BladeFileExtractor;
        $foundStrings = $bladeExtractor->extractFromFile($file);
        $this->assertContains('Start of nested structure', $foundStrings);
        $this->assertContains('Department: :name', $foundStrings);
        $this->assertContains('Employee: :name', $foundStrings);
        $this->assertContains('End of nested structure', $foundStrings);
    }

    #[Test]
    public function canHandleGetWithinBladePHP(): void
    {
        $code = <<<'CODE'
{{ __('Start of Blade PHP') }}
@php
$activeUsers = \App\Models\User::where('active', true)->get()->map(function($user) {
    return __('User: :name', ['name' => $user->name]);
})->implode(', ');
@endphp
<p>{{ __('Active users: :users', ['users' => $activeUsers]) }}</p>
{{ __('End of Blade PHP') }}
CODE;

        $file = $this->createTempFile('get_within_blade_php.blade.php', $code);

        $bladeExtractor = new BladeFileExtractor;
        $foundStrings = $bladeExtractor->extractFromFile($file);
        $this->assertContains('Start of Blade PHP', $foundStrings);
        $this->assertContains('User: :name', $foundStrings);
        $this->assertContains('Active users: :users', $foundStrings);
        $this->assertContains('End of Blade PHP', $foundStrings);
    }

    #[Test]
    public function canHandleGetWithinConditionals(): void
    {
        $code = <<<'CODE'
{{ __('Start of conditionals') }}
@if(\App\Models\Post::where('featured', true)->get()->isNotEmpty())
    <h2>{{ __('Featured Posts') }}</h2>
    @foreach(\App\Models\Post::where('featured', true)->get() as $post)
        <h3>{{ __('Post title: :title', ['title' => $post->title]) }}</h3>
    @endforeach
@else
    <p>{{ __('No featured posts found') }}</p>
@endif
{{ __('End of conditionals') }}
CODE;

        $file = $this->createTempFile('get_within_conditionals.blade.php', $code);

        $bladeExtractor = new BladeFileExtractor;
        $foundStrings = $bladeExtractor->extractFromFile($file);
        $this->assertContains('Start of conditionals', $foundStrings);
        $this->assertContains('Featured Posts', $foundStrings);
        $this->assertContains('Post title: :title', $foundStrings);
        $this->assertContains('No featured posts found', $foundStrings);
        $this->assertContains('End of conditionals', $foundStrings);
    }

    #[Test]
    public function canFindTranslationAfterGetInClassMethod(): void
    {
        $code = <<<'CODE'
<?php

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('active', true)->get();
        return view('users.index', [
            'title' => __('User List'),
            'users' => $users
        ]);
    }
}
CODE;

        $file = $this->createTempFile('Test.php', $code);

        $extractor = new PhpClassExtractor;
        $foundTranslations = $extractor->extractFromFile($file);
        $this->assertContains('User List', $foundTranslations);
    }

    #[Test]
    public function canFindTranslationAfterGetInStaticMethod(): void
    {
        $code = <<<'CODE'
<?php

class UserRepository
{
    public static function getActiveUsers()
    {
        $users = User::where('active', true)->get();
        foreach ($users as $user) {
            echo __('Active user: :name', ['name' => $user->name]);
        }
    }
}
CODE;

        $file = $this->createTempFile('UserRepository.php', $code);

        $extractor = new PhpClassExtractor;
        $foundTranslations = $extractor->extractFromFile($file);
        $this->assertContains('Active user: :name', $foundTranslations);
    }

    #[Test]
    public function canFindTranslationAfterGetInTraitMethod(): void
    {
        $code = <<<'CODE'
<?php

trait UserManagement
{
    public function getAllUsers()
    {
        $users = User::all()->filter(function($user) {
            return $user->posts()->get()->isNotEmpty();
        });
        return [
            'message' => __('Found :count active users', ['count' => $users->count()]),
            'users' => $users
        ];
    }
}
CODE;

        $file = $this->createTempFile('UserManagement.php', $code);

        $extractor = new PhpClassExtractor;
        $foundTranslations = $extractor->extractFromFile($file);

        $this->assertContains('Found :count active users', $foundTranslations);
    }

    #[Test]
    public function canFindTranslationAfterGetInClosureWithinMethod(): void
    {
        $code = <<<'CODE'
<?php

class PostController extends Controller
{
    public function getPopularPosts()
    {
        $posts = Post::all()->filter(function($post) {
            return $post->comments()->get()->count() > 10;
        });
        return view('posts.popular', [
            'title' => __('Popular Posts'),
            'posts' => $posts
        ]);
    }
}
CODE;

        $file = $this->createTempFile('PostController.php', $code);

        $extractor = new PhpClassExtractor;
        $foundTranslations = $extractor->extractFromFile($file);
        $this->assertContains('Popular Posts', $foundTranslations);
    }

    #[Test]
    public function canFindTranslationAfterGetInAbstractClassMethod(): void
    {
        $code = <<<'CODE'
<?php

abstract class BaseRepository
{
    protected abstract function getModel();

    public function getAll()
    {
        $items = $this->getModel()::get();
        return [
            'message' => __(':count items retrieved', ['count' => $items->count()]),
            'items' => $items
        ];
    }
}
CODE;

        $file = $this->createTempFile('BaseRepository.php', $code);

        $extractor = new PhpClassExtractor;
        $foundTranslations = $extractor->extractFromFile($file);
        $this->assertContains(':count items retrieved', $foundTranslations);
    }
}

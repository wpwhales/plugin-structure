<?php

namespace Tests\Auth;

use WPWCore\Auth\Access\AuthorizationException;
use WPWCore\Auth\Access\Gate;
use WPWCore\Auth\Middleware\Authorize;
use WPWhales\Container\Container;
use WPWhales\Contracts\Auth\Access\Gate as GateContract;
use WPWhales\Contracts\Routing\Registrar;
use WPWhales\Database\Eloquent\Model;
use WPWhales\Events\Dispatcher;
use WPWhales\Http\Request;
use WPWhales\Routing\CallableDispatcher;
use WPWhales\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use WPWhales\Routing\Middleware\SubstituteBindings;
use WPWhales\Routing\Router;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use stdClass;

class AuthorizeMiddlewareTest extends \WP_UnitTestCase
{
    protected $container;
    protected $user;
    protected $router;

    public function set_up()
    {
        parent::set_up();

        $this->user = new stdClass;



        $this->app->singleton(GateContract::class, function () {
            return new Gate($this->app, function () {
                return $this->user;
            });
        });

        $this->router = $this->app->router;

        $this->app->bind(CallableDispatcherContract::class, fn($app) => new CallableDispatcher($app));

        $this->app->singleton(Registrar::class, function () {
            return $this->router;
        });
    }


    public function tear_down()
    {
        m::close();

    }

    public function testItCanGenerateDefinitionViaStaticMethod()
    {
        $signature = (string)Authorize::using('ability');
        $this->assertSame('WPWCore\Auth\Middleware\Authorize:ability', $signature);

        $signature = (string)Authorize::using('ability', 'model');
        $this->assertSame('WPWCore\Auth\Middleware\Authorize:ability,model', $signature);

        $signature = (string)Authorize::using('ability', 'model', \App\Models\Comment::class);
        $this->assertSame('WPWCore\Auth\Middleware\Authorize:ability,model,App\Models\Comment', $signature);
    }

    public function testSimpleAbilityUnauthorized()
    {


        $this->gate()->define('view-dashboard', function ($user, $additional = null) {
            $this->assertNull($additional);

            return false;
        });

        $this->router->get('dashboard', [
            'middleware' => [Authorize::class . ':view-dashboard'],
           function () {
                return 'success';
            },
        ]);

        $response = $this->call("GET","dashboard");

      $exception = $response->exception->getPrevious();
        $this->assertInstanceOf(AuthorizationException::class,$exception);
        $this->assertSame($exception->getMessage(),'This action is unauthorized.');
    }

    public function testSimpleAbilityAuthorized()
    {
        $this->gate()->define('view-dashboard', function ($user) {
            return true;
        });

        $this->router->get('dashboard', [
            'middleware' => [Authorize::class . ':view-dashboard'],
             function () {
                return 'success';
            },
        ]);

        $response = $this->call("GET","dashboard");

        $this->assertSame('success', $response->getContent());
    }

    public function testSimpleAbilityWithStringParameter()
    {
        $this->gate()->define('view-dashboard', function ($user, $param) {
            return $param === 'some string';
        });


        $this->router->get('dashboard', [
            'middleware' => Authorize::class . ':view-dashboard,"some string"',
            function () {
                return 'success';
            },
        ]);

        $response = $this->call("GET","dashboard");

        $this->assertSame('success', $response->getContent());
    }

    public function testSimpleAbilityWithNullParameter()
    {
        $this->gate()->define('view-dashboard', function ($user, $param = null) {
            $this->assertNull($param);

            return true;
        });

        $this->router->get('dashboard', [
            'middleware' => Authorize::class . ':view-dashboard,null',
            function () {
                return 'success';
            },
        ]);

        $response = $this->call("GET","dashboard");
    }

    private function testSimpleAbilityWithOptionalParameter()
    {
        $post = new stdClass;

        $this->router->bind('post', function () use ($post) {
            return $post;
        });

        $this->gate()->define('view-comments', function ($user, $model = null) {
            return true;
        });

        $middleware = [SubstituteBindings::class, Authorize::class . ':view-comments,post'];

        $this->router->get('comments', [
            'middleware' => $middleware,
            'uses'       => function () {
                return 'success';
            },
        ]);
        $this->router->get('posts/{post}/comments', [
            'middleware' => $middleware,
            'uses'       => function () {
                return 'success';
            },
        ]);

        $response = $this->router->dispatch(Request::create('posts/1/comments', 'GET'));
        $this->assertSame('success', $response->content());

        $response = $this->router->dispatch(Request::create('comments', 'GET'));
        $this->assertSame('success', $response->content());
    }

    private function testSimpleAbilityWithStringParameterFromRouteParameter()
    {
        $this->gate()->define('view-dashboard', function ($user, $param) {
            return $param === 'true';
        });

        $this->router->get('dashboard/{route_parameter}', [
            'middleware' => Authorize::class . ':view-dashboard,route_parameter',
            'uses'       => function () {
                return 'success';
            },
        ]);

        $response = $this->router->dispatch(Request::create('dashboard/true', 'GET'));

        $this->assertSame('success', $response->content());
    }

    private function testSimpleAbilityWithStringParameter0FromRouteParameter()
    {
        $this->gate()->define('view-dashboard', function ($user, $param) {
            return $param === '0';
        });

        $this->router->get('dashboard/{route_parameter}', [
            'middleware' => Authorize::class . ':view-dashboard,route_parameter',
            'uses'       => function () {
                return 'success';
            },
        ]);

        $response = $this->router->dispatch(Request::create('dashboard/0', 'GET'));

        $this->assertSame('success', $response->content());
    }

    private function testModelTypeUnauthorized()
    {
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('This action is unauthorized.');

        $this->gate()->define('create', function ($user, $model) {
            $this->assertSame('App\User', $model);

            return false;
        });

        $this->router->get('users/create', [
            'middleware' => [SubstituteBindings::class, Authorize::class . ':create,App\User'],
            'uses'       => function () {
                return 'success';
            },
        ]);

        $this->router->dispatch(Request::create('users/create', 'GET'));
    }

    private function testModelTypeAuthorized()
    {
        $this->gate()->define('create', function ($user, $model) {
            $this->assertSame('App\User', $model);

            return true;
        });

        $this->router->get('users/create', [
            'middleware' => Authorize::class . ':create,App\User',
            'uses'       => function () {
                return 'success';
            },
        ]);

        $response = $this->router->dispatch(Request::create('users/create', 'GET'));

        $this->assertSame('success', $response->content());
    }

    private function testModelUnauthorized()
    {
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('This action is unauthorized.');

        $post = new stdClass;

        $this->router->bind('post', function () use ($post) {
            return $post;
        });

        $this->gate()->define('edit', function ($user, $model) use ($post) {
            $this->assertSame($model, $post);

            return false;
        });

        $this->router->get('posts/{post}/edit', [
            'middleware' => [SubstituteBindings::class, Authorize::class . ':edit,post'],
            'uses'       => function () {
                return 'success';
            },
        ]);

        $this->router->dispatch(Request::create('posts/1/edit', 'GET'));
    }

    private function testModelAuthorized()
    {
        $post = new stdClass;

        $this->router->bind('post', function () use ($post) {
            return $post;
        });

        $this->gate()->define('edit', function ($user, $model) use ($post) {
            $this->assertSame($model, $post);

            return true;
        });

        $this->router->get('posts/{post}/edit', [
            'middleware' => [SubstituteBindings::class, Authorize::class . ':edit,post'],
            'uses'       => function () {
                return 'success';
            },
        ]);

        $response = $this->router->dispatch(Request::create('posts/1/edit', 'GET'));

        $this->assertSame('success', $response->content());
    }

    private function testModelInstanceAsParameter()
    {
        $instance = m::mock(Model::class);

        $this->gate()->define('success', function ($user, $model) use ($instance) {
            $this->assertSame($model, $instance);

            return true;
        });

        $request = m::mock(Request::class);

        $nextParam = null;

        $next = function ($param) use (&$nextParam) {
            $nextParam = $param;
        };

        (new Authorize($this->gate()))
            ->handle($request, $next, 'success', $instance);
    }

    /**
     * Get the Gate instance from the container.
     *
     * @return \WPWCore\Auth\Access\Gate
     */
    protected function gate()
    {
        return $this->app->make(GateContract::class);
    }
}

<?php

namespace Tests\Console\View;

use WPWCore\Console\OutputStyle;
use WPWCore\Console\View\Components;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ComponentsTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testAlert()
    {
        $output = new BufferedOutput();

        \WPWCore\Support\with(new Components\Alert($output))
            ->render('The application is in the [production] environment');

        $this->assertStringContainsString(
            'THE APPLICATION IS IN THE [PRODUCTION] ENVIRONMENT.',
            $output->fetch()
        );
    }

    public function testBulletList()
    {
        $output = new BufferedOutput();

        \WPWCore\Support\with(new Components\BulletList($output))
            ->render([
                'ls -la',
                'php artisan inspire',
            ]);

        $output = $output->fetch();

        $this->assertStringContainsString('⇂ ls -la', $output);
        $this->assertStringContainsString('⇂ php artisan inspire', $output);
    }

    public function testError()
    {
        $output = new BufferedOutput();

        \WPWCore\Support\with(new Components\Error($output))
            ->render('The application is in the [production] environment');

        $this->assertStringContainsString('ERROR  The application is in the [production] environment.', $output->fetch());
    }

    public function testInfo()
    {
        $output = new BufferedOutput();

        \WPWCore\Support\with(new Components\Info($output))
            ->render('The application is in the [production] environment');

        $this->assertStringContainsString('INFO  The application is in the [production] environment.', $output->fetch());
    }

    public function testConfirm()
    {
        $output = m::mock(OutputStyle::class);

        $output->shouldReceive('confirm')
               ->with('Question?', false)
               ->once()
               ->andReturnTrue();

        $result = \WPWCore\Support\with(new Components\Confirm($output))
            ->render('Question?');
        $this->assertTrue($result);

        $output->shouldReceive('confirm')
               ->with('Question?', true)
               ->once()
               ->andReturnTrue();

        $result = \WPWCore\Support\with(new Components\Confirm($output))
            ->render('Question?', true);
        $this->assertTrue($result);
    }

    public function testChoice()
    {
        $output = m::mock(OutputStyle::class);

        $output->shouldReceive('askQuestion')
               ->with(m::type(ChoiceQuestion::class))
               ->once()
               ->andReturn('a');

        $result = \WPWCore\Support\with(new Components\Choice($output))
            ->render('Question?', ['a', 'b']);
        $this->assertSame('a', $result);
    }

    public function testTask()
    {
        $output = new BufferedOutput();

        \WPWCore\Support\with(new Components\Task($output))
            ->render('My task', fn() => true);
        $result = $output->fetch();
        $this->assertStringContainsString('My task', $result);
        $this->assertStringContainsString('DONE', $result);

        \WPWCore\Support\with(new Components\Task($output))
            ->render('My task', fn() => false);
        $result = $output->fetch();
        $this->assertStringContainsString('My task', $result);
        $this->assertStringContainsString('FAIL', $result);
    }

    public function testTwoColumnDetail()
    {
        $output = new BufferedOutput();

        \WPWCore\Support\with(new Components\TwoColumnDetail($output))
            ->render('First', 'Second');
        $result = $output->fetch();
        $this->assertStringContainsString('First', $result);
        $this->assertStringContainsString('Second', $result);
    }

    public function testWarn()
    {
        $output = new BufferedOutput();

        \WPWCore\Support\with(new Components\Warn($output))
            ->render('The application is in the [production] environment');

        $this->assertStringContainsString('WARN  The application is in the [production] environment.', $output->fetch());
    }
}

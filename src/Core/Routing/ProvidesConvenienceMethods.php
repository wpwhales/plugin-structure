<?php

namespace WPWCore\Routing;

use Closure as BaseClosure;
use WPWhales\Contracts\Auth\Access\Gate;
use WPWhales\Contracts\Bus\Dispatcher;
use WPWhales\Http\JsonResponse;
use WPWhales\Http\Request;
use WPWhales\Support\Str;
use WPWhales\Validation\ValidationException;
use WPWhales\Validation\Validator;

trait ProvidesConvenienceMethods
{
    /**
     * The response builder callback.
     *
     * @var \Closure
     */
    protected static $responseBuilder;

    /**
     * The error formatter callback.
     *
     * @var \Closure
     */
    protected static $errorFormatter;

    /**
     * Set the response builder callback.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function buildResponseUsing(BaseClosure $callback)
    {
        static::$responseBuilder = $callback;
    }

    /**
     * Set the error formatter callback.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function formatErrorsUsing(BaseClosure $callback)
    {
        static::$errorFormatter = $callback;
    }

    /**
     * Validate the given request with the given rules.
     *
     * @param  \WPWhales\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array
     *
     * @throws \WPWhales\Validation\ValidationException
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        return $this->extractInputFromRules($request, $rules);
    }

    /**
     * Get the request input based on the given validation rules.
     *
     * @param  \WPWhales\Http\Request  $request
     * @param  array  $rules
     * @return array
     */
    protected function extractInputFromRules(Request $request, array $rules)
    {
        return $request->only(collect($rules)->keys()->map(function ($rule) {
            return Str::contains($rule, '.') ? explode('.', $rule)[0] : $rule;
        })->unique()->toArray());
    }

    /**
     * Throw the failed validation exception.
     *
     * @param  \WPWhales\Http\Request  $request
     * @param  \WPWhales\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \WPWhales\Validation\ValidationException
     */
    protected function throwValidationException(Request $request, $validator)
    {
        throw new ValidationException($validator, $this->buildFailedValidationResponse(
            $request, $this->formatValidationErrors($validator)
        ));
    }

    /**
     * Build a response based on the given errors.
     *
     * @param  \WPWhales\Http\Request  $request
     * @param  array  $errors
     * @return \WPWhales\Http\JsonResponse|mixed
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        if (isset(static::$responseBuilder)) {
            return (static::$responseBuilder)($request, $errors);
        }

        return new JsonResponse($errors, 422);
    }

    /**
     * Format validation errors.
     *
     * @param  \WPWhales\Validation\Validator  $validator
     * @return array|mixed
     */
    protected function formatValidationErrors(Validator $validator)
    {
        if (isset(static::$errorFormatter)) {
            return (static::$errorFormatter)($validator);
        }

        return $validator->errors()->getMessages();
    }

    /**
     * Authorize a given action against a set of arguments.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return \WPWCore\Auth\Access\Response
     *
     * @throws \WPWCore\Auth\Access\AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);

        return \WPWCore\app(Gate::class)->authorize($ability, $arguments);
    }

    /**
     * Authorize a given action for a user.
     *
     * @param  \WPWhales\Contracts\Auth\Authenticatable|mixed  $user
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return \WPWCore\Auth\Access\Response
     *
     * @throws \WPWCore\Auth\Access\AuthorizationException
     */
    public function authorizeForUser($user, $ability, $arguments = [])
    {
        [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);

        return \WPWCore\app(Gate::class)->forUser($user)->authorize($ability, $arguments);
    }

    /**
     * Guesses the ability's name if it wasn't provided.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return array
     */
    protected function parseAbilityAndArguments($ability, $arguments)
    {
        if (is_string($ability)) {
            return [$ability, $arguments];
        }

        return [debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'], $ability];
    }

    /**
     * Dispatch a job to its appropriate handler.
     *
     * @param  mixed  $job
     * @return mixed
     */
    public function dispatch($job)
    {
        return \WPWCore\app(Dispatcher::class)->dispatch($job);
    }

    /**
     * Dispatch a command to its appropriate handler in the current process.
     *
     * @param  mixed  $job
     * @param  mixed  $handler
     * @return mixed
     */
    public function dispatchNow($job, $handler = null)
    {
        return \WPWCore\app(Dispatcher::class)->dispatchNow($job, $handler);
    }

    /**
     * Get a validation factory instance.
     *
     * @return \WPWhales\Contracts\Validation\Factory
     */
    protected function getValidationFactory()
    {
        return \WPWCore\app('validator');
    }
}

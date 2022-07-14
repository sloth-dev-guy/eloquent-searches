<?php

namespace SlothDevGuy\Searches\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Trait RequestValidate
 * @package GICU\DataGathering\Requests
 */
trait RequestValidate
{
    /**
     * @var Validator
     */
    protected Validator $validator;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function rules() : array
    {
        return [];
    }

    /**
     * Get the validation messages used for each rule.
     *
     * @return array
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Get the validation attributes used during the instance build
     *
     * @return array
     */
    protected function attributes(): array
    {
        return [];
    }

    /**
     * Validate the class instance.
     *
     * @return void
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function validate(): void
    {
        $this->prepareForValidation();

        if (!$this->authorize()) {
            $this->failedAuthorization();
        }

        $validator = $this->getValidatorInstance();

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $this->passedValidation();
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // no default action
    }

    /**
     * Determine if the request passes the authorization check.
     *
     * @return bool
     */
    protected function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     * @throws AuthorizationException
     */
    protected function failedAuthorization(): void
    {
        throw new AuthorizationException();
    }

    /**
     * Get the validator instance for the request.
     *
     * @return Validator
     */
    protected function getValidatorInstance() : Validator
    {
        $this->validator = validator($this->all(), $this->rules(), $this->messages(), $this->attributes());;

        return $this->validator;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new ValidationException($validator, $this->errorResponse());
    }

    /**
     * Handle a passed validation attempt.
     *
     * @return void
     */
    protected function passedValidation()
    {
        //
    }

    /**
     * Get the validated data from the request.
     *
     * @return array
     * @throws ValidationException
     */
    public function validated(): array
    {
        return $this->validator->validated();
    }

    /**
     * Returns an error json response with all failed validations
     *
     * @param array $options
     * @return JsonResponse
     */
    protected function errorResponse(array $options = []): JsonResponse
    {
        $options = array_merge([
            'message' => 'the request input was invalid',
            'status_code' => 422,
        ], $options);

        return response()->json([
            'message' => $options['message'],
            'errors' => $this->validator->errors()->messages(),
        ], $options['status_code']);
    }
}

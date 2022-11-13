<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransformInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $transform)
    {
        $transformedInput = [];

        foreach ($request->request->all() as $input => $value) {
            $attribute = $transform::originalAttribute($input);
            if (isset($attribute)) {
                $transformedInput[$attribute] = $value;
            }
        }

        $request->replace($transformedInput);

        $response = $next($request);

        //Transformamos los datos en los casos que ocurran errores de validacion (Request)
        if (isset($response->exception) && $response->exception instanceof ValidationException) {

            $data = $response->getData();
            $transformedErrors = [];
            foreach ($data->error[0]->detail as $field => $error) {
                $transformedField = $transform::transformedAttribute($field);

                $transformedErrors[] = [
                    "status" => $data->error[0]->status,
                    "inputName" => $transformedField,
                    "detail" => str_replace($field, $transformedField, $error[0])
                ];
            }

            $data->error = $transformedErrors;
            $response->setData($data);
        }

        return $response;
    }
}

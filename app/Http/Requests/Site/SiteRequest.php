<?php

namespace App\Http\Requests\Site;

use App\Http\Requests\Request;

class SiteRequest extends Request {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $tabs = $this->get('tabs');

        switch ($tabs) {
            case 'settings:photo': {
                return [
                    'photo' => 'required|mimes:jpg,jpeg,png,gif,bmp'
                ];
            }
            default: { // Create + settings:info rules
                return [
                    'code'      => 'required',
                    'name'      => 'required',
                    'address'   => 'required',
                    'suburb'    => 'required',
                    'state'     => 'required',
                    'postcode'  => 'required',
                ];
            }
        }
    }

    public function messages()
    {
        return [
            'code.required'      => 'The site no. field is required.',
            'client_id.required' => 'The client field is required.',
            'photo'              => 'No image was selected.',
        ];
    }

    /**
     * Overide the default return URL form failed validation request
     * with custom site/{slug}/settings/info
     *
     * @param array $errors
     * @return $this|JsonResponse
     */
    public function response(array $errors)
    {
        // Optionally, send a custom response on authorize failure
        // (default is to just redirect to initial page with errors)
        //
        // Can return a response, a view, a redirect, or whatever else

        /*
        if ($this->ajax() || $this->wantsJson()) {
            return new JsonResponse($errors, 422);
        }

        $slug = $this->get('slug');
        $tabs = $this->get('tabs');

        switch ($tabs) {
            case 'settings:info':
                return $this->redirector->to('site/' . $slug . '/settings/info')
                    ->withInput($this->except($this->dontFlash))
                    ->withErrors($errors, $this->errorBag);
            case 'settings:photo':
                return $this->redirector->to('site/' . $slug . '/settings/photo')
                    ->withInput($this->except($this->dontFlash))
                    ->withErrors($errors, $this->errorBag);
            default:
                return $this->redirector->to($this->getRedirectUrl())
                    ->withInput($this->except($this->dontFlash))
                    ->withErrors($errors, $this->errorBag);
        }*/

    }
}

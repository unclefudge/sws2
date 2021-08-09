<?php

namespace App\Http\Requests\Site;

use App\Http\Requests\Request;

class SiteCheckinRequest extends Request {

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
        return [
            'question1'  => 'required_with:checkin',
            'question2'  => 'required_with:checkin,checkinStore,checkinTruck',
            'question3'  => 'required_with:checkin,checkinTruck',
            'question4' => 'required_with:checkin',
            'question5'  => 'required_with:checkin',
            'question6'  => 'required_with:checkin',
            'question7'  => 'required_with:checkin,checkinStore',
            'question8'  => 'required_with:checkin',
            'question9'  => 'required_with:checkinStore',
            'question10' => 'required_with:checkinStore',
            'question11' => 'required_with:checkinStore',
            'question12' => 'required_with:checkinStore',
            'question13' => 'required_with:checkinStore',
            'question14' => 'required_with:checkinTruck',
            //'question18'  => 'required_with:checkin,checkinStore,checkinTruck',
            'question19'  => 'required_with:checkin',
            'question20'  => 'required_with:checkin,checkinStore,checkinTruck',
            'question21'  => 'required_with:checkin,checkinStore,checkinTruck',
            'question22'  => 'required_with:checkin,checkinStore,checkinTruck',
            'location'   => 'required_without:safe_site',
            'rating'     => 'required_without:safe_site',
            'reason'     => 'required_without:safe_site',
            'action'     => 'required_without:safe_site',
            'media'      => 'mimes:jpg,jpeg,png,gif,bmp,m4v,avi,flv,mp4,mov',
        ];
    }


    public function messages()
    {
        return [
            'question1.required_with'  => 'Please acknowledge you have read the Site Specific Health & Safety Rules.',
            'question2.required_with'  => 'Please acknowledge you are fit for work.',
            'question3.required_with'  => 'Please acknowledge you not affected by any pre-existing medical condition.',
            'question4.required_with'  => 'Please acknowledge you are physically present on the above Worksite',
            'question5.required_with'  => 'Please acknowledge you familiar with the site specific Risk Assessment.',
            'question6.required_with'  => 'Please acknowledge you will take action to eliminate or control any hazards.',
            'question7.required_with'  => 'Please acknowledge you will report all incidents, near misses, unsafe work practices and conditions.',
            'question8.required_with'  => 'Please acknowledge you will leave the site secure and safe for others.',
            'question9.required_with'  => 'Please acknowledge you will store all materials safely.',
            'question10.required_with' => 'Please acknowledge you will assess your tasks and implement controls as necessary.',
            'question11.required_with' => 'Please acknowledge you will ensure all safety devices such as handrails are in place.',
            'question12.required_with' => 'Please acknowledge you will practice good housekeeping.',
            'question13.required_with' => 'Please acknowledge you will ensure the site is left secure, is safe for others.',
            'question14.required_with' => 'Please acknowledge you hold a current license.',
            'question15.required_with' => 'Please acknowledge you will abide by the road rules and be a courteous & responsible driver.',
            'question16.required_with' => 'Please acknowledge you report any damage and defects to the vehicle.',
            'question17.required_with' => 'Please acknowledge you any maintenance requirements (including servicing requirements)',
            'question18.required_with' => 'Please acknowledge you will adhere to the principles required to manage the risk of COVID-19',
            'question19.required_with' => 'Please acknowledge you will not make changes to any scaffold or Fall Protection',
            'question20.required_with' => 'Please acknowledge you have signed in to the NSW service Covid safe checkin',
            'question21.required_with' => 'Please acknowledge you wear a mask and observe Gov directives',
            'question22.required_with' => 'Please acknowledge you understand the current NSW Health orders and will comply with its requirements in relation covid',
            'location.required_without'  => 'Please provide the location of hazard.',
            'rating.required_without'  => 'Please provide the risk rating of hazard.',
            'reason.required_without'  => 'Please provide the reason for unsafe worksite.',
            'action.required_without'  => 'Please provide the actions to have taken to make the site safe.',
        ];
    }
}

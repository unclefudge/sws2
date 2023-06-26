<?php

namespace App\Http\Requests\Site;

use App\Http\Requests\Request;

class SiteAsbestosRequest extends Request {

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
    /*
     *

  "isolation" => "iso"
  "register" => "1"
  "swms" => "1"
  "inspection" => "1"
  "supervisor_id" => "5"
     */
    public function rules()
    {
        return [
            'site_id'         => 'required',
            'client_name'     => 'required',
            'client_phone'    => 'required',
            'supervisor_id'   => 'required',
            'super_phone'     => 'required',
            'amount'          => 'required',
            'friable'         => 'required',
            'type'            => 'required',
            'type_other'      => 'required_if:type,other',
            'location'        => 'required',
            'date_from'       => 'required',
            'date_to'         => 'required',
            'hours_from'      => 'required',
            'hours_to'        => 'required',
            'workplace'       => 'required',
            'removalist_name' => 'required_if:removalist,other',
            'workers'         => 'required_if:removalist,3',
            'coalmine'        => 'required_if:removalist,3',
            'hygiene_report'  => 'required_if:hygiene,1',
            //'assessor_name'  => 'required_if:friable,0',
            //'assessor_phone' => 'required_if:friable,0',
            //'assessor_cert'  => 'required_if:friable,0',
            //'assessor_lic'   => 'required_if:friable,0',
            //'assessor_dept'  => 'required_if:friable,0',
            //'assessor_state' => 'required_if:friable,0',
            'equip'           => 'required_if:removalist,3',
            'method'          => 'required_if:removalist,3',
            'isolation'       => 'required_if:removalist,3',
            'register'        => 'required_if:friable,0|not_in:0',
            'swms'            => 'required_if:friable,0|not_in:0',
            //'inspection' => 'required_if:friable,0|required_if:amount_over,1|not_in:0',
        ];
    }

    public function messages()
    {
        return [
            'site_id.required'           => 'The site field is required',
            'client_name.required'       => 'The client name field is required',
            'client_phone.required'      => 'The client phone field is required',
            'supervisor_id.required'     => 'The supervisor field is required',
            'super_phone.required'       => 'The supervisor phone field is required',
            'friable.required'           => 'The class field is required',
            'type_other.required'        => 'The other type field is required',
            'date_from.required'         => 'The dates field is required',
            'hours_from.required'        => 'The open from field is required',
            'hours_to.required'          => 'The open to field is required',
            'assessor_name'              => 'The assessor name field is required',
            'assessor_phone'             => 'The assessor phone field is required',
            'assessor_cert'              => 'The assessor qualification field is required',
            'assessor_lic'               => 'The assessor licence field is required',
            'assessor_dept'              => 'The assessor department field is required',
            'assessor_state'             => 'The assessor state field is required',
            'workers.required_if'        => 'The number of workers field is required',
            'hygiene_report.required_if' => 'The report type field is required',
            'equip.required_if'          => 'At least one protective equipment field is required',
            'method.required_if'         => 'At least one method field is required',
            'isolation.required_if'      => 'The extent of isolation field is required',
            'register.required_if'       => 'The asbestos register reviewed field is required',
            'register.not_in'            => 'The asbestos register reviewed field can not be NO',
            'swms.required_if'           => 'The SWMS confirmation field is required',
            'swms.not_in'                => 'The SWMS confirmation field can not be NO',
            'inspection.required_if'     => 'The inspection confirmation field is required',
        ];
    }
}

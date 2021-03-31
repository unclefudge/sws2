<?php

/*
 * Global Variables
 */

define("VALID_EMAIL_PATTERN", '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD');
define('TODO_TYPES', ['swms'                  => 'Safe Work Method Statements',
                      'qa'                    => 'Quality Assurance Reports',
                      'toolbox'               => 'Toolbox Talks',
                      'equipment'             => 'Equipment Transfer',
                      'maintenance'           => 'Site Maintenance Requests',
                      'inspection_electrical' => 'Electrical Inspection Reports',
                      'inspection_plumbing'   => 'Plumbing Inspection Reports',
                      'user doc'              => 'User Documents']);

function validEmail($email)
{
    return preg_match('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', $email);
}

function flash($title = null, $message = null)
{
    $flash = app('App\Http\Flash');

    if (func_num_args() == 0) {
        return $flash; // $flash
    }

    return $flash->info($title, $message); // flash('Title', 'Mesg');
}

function fieldErrorMessage($field, $errors)
{
    if ($errors->has($field))
        return '<span class="help-block">' . $errors->first($field) . '</span>';
}

function fieldHasError($field, $errors)
{
    if ($errors->has($field))
        return 'has-error';
}

/* Custom Planner Sort functions */
function sortName($a, $b)
{
    if ($a['name'] == $b['name'])
        return 0;

    return ($a['name'] < $b['name']) ? - 1 : 1;
}

function sortEntityName($a, $b)
{
    if ($a['entity_name'] == $b['entity_name'])
        return 0;

    return ($a['entity_name'] < $b['entity_name']) ? - 1 : 1;
}

function sortSiteName($a, $b)
{
    if ($a['site_name'] == $b['site_name'])
        return 0;

    return ($a['site_name'] < $b['site_name']) ? - 1 : 1;
}

/* Flattens a multi-dimesion arrary recursively into a flat array */
function flatten_array($arg)
{
    return is_array($arg) ? array_reduce($arg, function ($c, $a) {
        return array_merge($c, flatten_array($a));
    }, []) : [$arg];
}

/*
function companyTradesSBC ($slug) {

    $company = \App\Company::where('slug', '=', $slug)->firstOrFail();
    $trades = '';

    foreach($company->trades as $trade)
        $trades .= $trade->name . ', ';

    return rtrim($trades, ', ');
}
*/

/**
 * Generate a unique slug.
 * If it already exists, a number suffix will be appended.
 * It probably works only with MySQL.
 *
 * @link http://chrishayes.ca/blog/code/laravel-4-generating-unique-slugs-elegantly
 *
 * @param Illuminate\Database\Eloquent\Model $model
 * @param string $value
 * @return string
 */
function getUniqueSlug(\Illuminate\Database\Eloquent\Model $model, $value)
{
    $slug = \Illuminate\Support\Str::slug($value);
    if ($model->slug == $slug)
        return $slug;

    $slugCount = count($model->whereRaw("slug REGEXP '^{$slug}(-[0-9]+)?$' and id != '{$model->id}'")->get());

    return ($slugCount > 0) ? "{$slug}-{$slugCount}" : $slug;
}


/**
 * Create a array of all permission types
 *
 * @return array
 */
function permOptions($action, $type = '')
{

    $array = [];

    if ($action == 'add') $array = ['0' => 'No', '1' => 'Create'];
    if ($type == 'up') $array = ['0' => 'No', '1' => 'Upload'];
    if ($action == 'del') {
        if ($type == 'del') $array = ['0' => 'No', '1' => 'Delete'];
        if ($type == 'arc') $array = ['0' => 'No', '1' => 'Archive'];
        if ($type == 'res') $array = ['0' => 'No', '1' => 'Resolve'];
    }
    if ($action == 'sig') $array = ['0' => 'No', '1' => 'Sign Off'];
    if ($action == 'view' || $action == 'edit') {
        $t = ucfirst($action);
        switch ($type) {
            case 'all' :
                $array = ['0' => 'No', '99' => "All"];
                break;
            case 'our' :
                $array = ['0' => 'No', '99' => "All", '50' => "Our Company"];
                break;
            case 'own' :
                $array = ['0' => 'No', '99' => "All", '20' => "Own Company"];
                break;
            case 'super' :
                $array = ['0' => 'No', '99' => "All", '40' => 'Supervisor for'];
                break;
            case 'super.plan' :
                $array = ['0' => 'No', '99' => "All", '40' => 'Supervisor for', '30' => 'Planned for'];
                break;
            case 'super.company' :
                $array = ['0' => 'No', '99' => "All", '40' => 'Supervisor for', '20' => 'Own Company'];
                break;
            case 'super.individual' :
                $array = ['0' => 'No', '99' => "All", '40' => 'Supervisor for', '10' => "Individual Only"];
                break;
            case 'company.individual' :
                $array = ['0' => 'No', '99' => "All", '20' => 'Own Company', '10' => "Individual Only"];
                break;
            case 'every-plan' :
                $array = ['0' => 'No', '99' => "All", '50' => "Our Company", '40' => 'Supervisor for', '20' => 'Own Company', '10' => "Individual Only"];
                break;
            case 'every' :
                $array = ['0' => 'No', '99' => "All", '50' => "Our Company", '40' => 'Supervisor for', '30' => 'Planned for', '20' => 'Own Company', '10' => "Individual Only"];
                break;
            default :
                $array = ['0' => 'No', '99' => "All", '50' => "Our Company", '40' => 'Supervisor for', '30' => 'Planned for', '20' => 'Own Company', '10' => "Individual Only"];
        }
    }

    return $array;
}


/**
 * Create Select field for Permission
 *
 * @return string
 */
function permSelect($permission, $type, $model, $company_id, $disable = false)
{
    $permission_id = \App\Models\Misc\Permission2::where('slug', $permission)->first()->id;
    list($action, $rest) = explode('.', $permission);

    // If User Model get Users Roles Level
    $user_model = false;
    if (Schema::hasColumn($model->getTable(), 'username')) {
        $user_model = true;
        $user_role_level = $model->rolesPermissionLevel($permission, $company_id);
        $model_level = $model->userPermissionLevel($permission, $company_id);
    } else
        $model_level = $model->permissionLevel($permission, $company_id);


    $array = permOptions($action, $type);
    $options = '';
    foreach ($array as $value => $text) {
        // For User Model only display options which are greater than a users role already grants
        if ($user_model) {
            if ($user_role_level <= $value) {
                $highest = ($user_role_level > $model_level) ? $user_role_level : $model_level;
                $options .= ($value == $highest) ? "<option value='$value' selected='selected'>$text</option>" : "<option value='$value'>$text</option>";
            }
        } else {
            // For Role Model display all options
            $options .= ($value == $model_level) ? "<option value='$value' selected='selected'>$text</option>" : "<option value='$value'>$text</option>";
        }
    }

    // Disable select if Max Level or Auth User not of same company as User model
    $disabled = ($disable || ($user_model && Auth::user()->company_id != $company_id)) ? 'disabled' : '';
    $ext = (Auth::user()->company_id != $company_id) ? 'ext' : '';
    $string = "<select class='form-control bs-select' name='p$ext$permission_id' $disabled>";
    $string .= $options;
    $string .= '</select>';

    return $string;
}

/**
 * Create a array of all permission types
 *
 * @return array
 */
function getPermissionTypes()
{
    $array = [];
    $permissions = DB::table('permissions')->get();
    foreach ($permissions as $permission)
        $array[$permission->slug] = $permission->id;

    return $array;
}

/*
 * Create Select field for Notifications
 *
 * @return string
 */
/*
 * moved function to DB class
function notificationSelect($id, $name, $pop_head = '', $pop_info = '', $help_mesg = '')
{
    $str = '<div class="row"><div class="col-md-12"><div class="form-group"><div class="col-md-3">';
    $str .= '<label for="type1" class="control-label">' . $name;
    if ($pop_head) {
        $str .= ' <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
        data-original-title="' . $pop_head . '" data-content="' . $pop_info . '"><i class="fa fa-question-circle font-grey-silver"></i></a>';
    }
    $str .= '</label></div><div class="col-md-9">';

    // Select Options
    $options = '';
    $selected = Auth::user()->company->notificationsUsersTypeArray($id);
    foreach (Auth::user()->company->staffSelect() as $value => $text) {
        $options .= (in_array($value, $selected)) ? "<option value='$value' selected='selected'>$text</option>" : "<option value='$value'>$text</option>";
    }

    $str .= '<select class="form-control select2" name="type' . $id . '[]" width="100%" multiple>';
    $str .= $options;
    $str .= '</select>';
    if ($help_mesg)
        $str .= '<span class="help-block">' . $help_mesg . '</span>';
    $str .= '</div></div></div></div><br>';

    return $str;
} */

/**
 * Create a array of all user emails with specific role
 *
 * @param array $role_ids
 * @return array
 */
function getUserEmailsWithRoles($role_ids)
{
    $email_array = [];
    $records = DB::table('role_user')->whereIn('role_id', $role_ids)->get();
    foreach ($records as $rec) {
        $user = \App\User::find($rec->user_id);
        if (validEmail($user->email))
            $email_array[] = $user->email;
    }

    return $email_array;
}

/**
 * Display dropdown option for Contractor Licences
 *
 * @param array $selected
 * @return strig
 */
/*
function contractorLicenceOptions($selected)
{
    $str = '';
    $str .= '<option></option><optgroup label="Building Work">';
    $str .= ($selected) ? '<option value="1">General building work</option>' : '<option value="1">General building work</option>';

    return $str;
}*/


function llllink_to($body, $path, $type)
{
    $csrf = csrf_field();

    if (is_object($path)) {
        $action = '/' . $path->getTable();

        if (in_array($type, ['PUT', 'PATCH', 'DELETE'])) {
            $action .= '/' . $path->getKey();
        }
    } else {
        $action = $path;
    }

    return <<<EOT
        <form method="POST" action="{$action}">
            <input type="hidden" name="_method" value="{$type}">
            $csrf
            <button type="submit">{$body}</button>
         </form>
EOT;
}

function scandir_datesort($dir)
{
    $ignored = array('.', '..', '.svn', '.htaccess');

    $files = array();
    foreach (scandir($dir) as $file) {
        if (in_array($file, $ignored)) continue;
        $files[$file] = filemtime($dir . '/' . $file);
    }

    arsort($files);
    $files = array_keys($files);

    return ($files) ? $files : false;
}

function format_expiry_field($date)
{
    if (!$date || get_class($date) != 'Illuminate\Support\Carbon')
        return "<b>Expiry:</b> N/A";
    elseif ($date->isPast())
        return "<span class='font-red'><b>Expired:</b> " . $date->format('d/m/Y') . "</span>";
    else
        return "<b>Expiry:</b> " . $date->format('d/m/Y');

}

function format_phone($country, $phone)
{
    $function = 'format_phone_' . $country;
    if (function_exists($function)) {
        return $function($phone);
    }

    return $phone;
}

function format_phone_au($phone)
{
    //if (!isset($phone{3})) {
    //    return '';
    //} // making sure we have something

    $stripped = preg_replace("/[^0-9]/", "", $phone);  // strip out everything but numbers
    $char2 = substr($stripped, 1, 1);
    $length = strlen($stripped);
    switch ($length) {
        case 8: // landline 9987 5423
            return preg_replace("/([0-9]{4})([0-9]{4})/", "$1 $2", $stripped);
            break;
        case 10:
            if ($char2 == '4') // mobile 0412 667 876
                return preg_replace("/([0-9]{4})([0-9]{3})([0-9]{3})/", "$1 $2 $3", $stripped);
            else               // landline (02) 9987 5423
                return preg_replace("/([0-9]{2})([0-9]{4})([0-9]{4})/", "($1) $2 $3", $stripped);
            break;
        default:
            return $phone;
            break;
    }
}

function format_phone_us($phone)
{
    //if (!isset($phone{3})) {
    //    return '';
    //}
    $stripped = preg_replace("/[^0-9]/", "", $phone);
    $length = strlen($stripped);
    switch ($length) {
        case 7:
            return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $stripped);
            break;
        case 10:
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $stripped);
            break;
        case 11:
            return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1($2) $3-$4", $stripped);
            break;
        default:
            return $phone;
            break;
    }
}

function format_abn($num)
{

    $stripped = preg_replace("/[^0-9]/", "", $num);
    $length = strlen($stripped);

    return preg_replace("/([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{3})/", "$1 $2 $3 $4", $stripped);
}

function reformatOldStr($str)
{
    $str = preg_replace('/&amp;/', '&', $str);
    $str = preg_replace('/&AMP;/', '&', $str);
    $str = preg_replace('/&#039;/', "'", $str);
    $str = preg_replace('/&quot;/', '"', $str);

    return $str;
}

function removeNullValues($array)
{
    $newArray = [];
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            if ($value !== null) {
                $newArray[$key] = $value;
            }
        }

        return $newArray;
    } else
        return $array;

}

/**
 * Function: sanitize
 * Returns a sanitized string, typically for URLs.
 *
 * Parameters:
 *     $string - The string to sanitize.
 *     $force_lowercase - Force the string to lowercase?
 *     $anal - If set to *true*, will remove all non-alphanumeric characters.
 */
function sanitizeFilename($string, $force_lowercase = false, $anal = false)
{
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
        "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
        "â€”", "â€“", ",", "<", ".", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;

    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ? mb_strtolower($clean, 'UTF-8') : strtolower($clean) :
        $clean;
}

/*
 * Next Work Date
 *
 * Parameters:
 *      $date - Carbon date
 *      $direction - either +/-
 *      $days - number of days
 */
function nextWorkDate($date, $direction, $days)
{
    // Determine next 'work' day ie mon-fri (x) days from given date
    // either before (-) or after (+) given date
    for ($i = 0; $i < $days; $i ++) {
        if ($direction == '+') {
            $date->addDays(1);
            if ($date->dayOfWeek == 6) // Skip Sat
                $date->addDays(2);
            if ($date->dayOfWeek == 0) // Skip Sun
                $date->addDays(1);
        } else {
            $date->subDays(1);
            if ($date->dayOfWeek == 6) // Skip Sat
                $date->subDays(1);
            if ($date->dayOfWeek == 0) // Skip Sun
                $date->subDays(2);
        }
    }

    return $date;
}

/*
 * This function takes two strings and formats them in a "diff" format that is familiar to most developers.
 * This function can bu used to compare lines of files, or properties of objects. But the root is this function:
 */
function get_decorated_diff($old, $new)
{
    $from_start = strspn($old ^ $new, "\0");
    $from_end = strspn(strrev($old) ^ strrev($new), "\0");

    $old_end = strlen($old) - $from_end;
    $new_end = strlen($new) - $from_end;

    $start = substr($new, 0, $from_start);
    $end = substr($new, $new_end);
    $new_diff = substr($new, $from_start, $new_end - $from_start);
    $old_diff = substr($old, $from_start, $old_end - $from_start);

    $new = "$start<ins style='background-color:#ccffcc'>$new_diff</ins>$end";
    $old = "$start<del style='background-color:#ffcccc'>$old_diff</del>$end";

    return array("old" => $old, "new" => $new);
}


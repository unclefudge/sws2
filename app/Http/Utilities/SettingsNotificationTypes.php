<?php

namespace App\Http\Utilities;

class SettingsNotificationTypes {

    protected static $settingsNotificationTypes = [
        '1'  => 'n.company.signup.sent',
        '2'  => 'n.company.signup.completed',
        '3'  => 'n.company.updated.details',
        '4'  => 'n.company.updated.business',
        '5'  => 'n.company.updated.trades',
        '6'  => 'n.user.created',
        '7'  => 'n.site.accident',
        '8'  => 'n.site.hazard',
        '9'  => 'n.site.asbestos',
        '10' => 'n.site.qa.handover',
        '11' => 'n.doc.whs.approval',
        '12' => 'n.doc.acc.approval',
        '13' => 'n.site.jobstart',
        '14' => 'n.swms.approval',
        '15' => 'n.site.status',
        '16' => 'n.site.jobstartexport',
        '17' => 'n.site.maintenance.completed',
        '18' => 'n.site.maintenance.noaction',
        '19' => 'n.site.maintenance.onhold',
        '20' => 'n.site.qa.super.photo',
        '21' => 'n.site.inspection.completed',
        '22' => 'n.site.qa.outstanding',
        '23' => 'n.site.qa.onhold',
        '24' => 'n.site.maintenance.executive',
        '25' => 'n.equipment.transfers'
    ];


    /**
     * @return array
     */
    public static function all()
    {
        return static::$settingsNotificationTypes;
    }

    /**
     * @return string
     */
    public static function name($id)
    {
        return static::$settingsNotificationTypes[$id];
    }

    /**
     * @return string
     */
    public static function type($name)
    {
        return array_search($name, static::$settingsNotificationTypes);
    }
}
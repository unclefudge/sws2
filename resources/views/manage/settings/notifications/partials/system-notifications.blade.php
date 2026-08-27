{{-- Existing event-driven notification recipient lists. --}}
@if (Auth::user()->company->subscription > 1)
    <h3 class="font-green form-section">Company Notifications</h3>
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.signup.sent')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.signup.completed')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.updated.details')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.updated.business')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.updated.creditorcode')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.updated.trades')->first()->notificationSelect() !!}
@endif

<h3 class="font-green form-section">Site Notifications</h3>
{!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.status')->first()->notificationSelect() !!}
{!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.accident')->first()->notificationSelect() !!}
{!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.hazard')->first()->notificationSelect() !!}
@if (Auth::user()->isCC())
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.asbestos')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.qa.handover')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.qa.super.photo')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'prac.completion.completed')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.maintenance.completed')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.inspection.completed')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.inspection.onhold')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.jobstart')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.planner.key.tasks')->first()->notificationSelect() !!}
@endif

<h3 class="font-green form-section">Document Notifications</h3>
{!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'doc.acc.approval')->first()->notificationSelect() !!}
{!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'doc.whs.approval')->first()->notificationSelect() !!}
{!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'swms.approval')->first()->notificationSelect() !!}
@if (Auth::user()->isCC())
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'doc.cc.approval')->first()->notificationSelect() !!}
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'doc.standard.renew')->first()->notificationSelect() !!}

    <h3 class="font-green form-section">Miscellaneous Notifications</h3>
    {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'user.archived.notifications')->first()->notificationSelect() !!}
@endif

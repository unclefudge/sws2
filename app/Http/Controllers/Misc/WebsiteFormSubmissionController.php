<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Misc\WebsiteFormSubmission;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class WebsiteFormSubmissionController extends Controller
{
    public function index()
    {
        return view('manage/settings/website-form-submission/list', ['formOptions' => $this->formOptions(), 'statusOptions' => $this->statusOptions(),]);
    }

    public function getSubmissions(Request $request)
    {
        $records = WebsiteFormSubmission::query()
            ->select(['id', 'uuid', 'form_key', 'status', 'step', 'email', 'full_name', 'phone', 'suburb',
                'postcode', 'state', 'rejection_reason', 'zoho_lead_id', 'zoho_status', 'created_at', 'updated_at',
            ]);

        if ($request->filled('form_key') && $request->get('form_key') !== 'all') {
            $records->where('form_key', $request->get('form_key'));
        }

        if ($request->filled('status') && $request->get('status') !== 'all') {
            $records->where('status', $request->get('status'));
        }

        if ($request->filled('search_text')) {
            $search = trim((string)$request->get('search_text'));

            $records->where(function ($query) use ($search) {
                $query->where('email', 'like', '%' . $search . '%')
                    ->orWhere('full_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('suburb', 'like', '%' . $search . '%')
                    ->orWhere('postcode', 'like', '%' . $search . '%')
                    ->orWhere('zoho_lead_id', 'like', '%' . $search . '%')
                    ->orWhere('uuid', 'like', '%' . $search . '%');
            });
        }

        return Datatables::of($records)
            ->addColumn('view', function ($record) {
                return '<div class="text-center"><a href="/settings/website-form-submission/' . $record->id . '"><i class="fa fa-eye"></i></a></div>';
            })
            ->editColumn('form_key', function ($record) {
                return $this->formLabel($record->form_key);
            })
            ->editColumn('status', function ($record) {
                return $this->statusLabel($record->status);
            })
            ->editColumn('zoho_status', function ($record) {
                if (!$record->zoho_status) {
                    return '';
                }

                return $record->zoho_status === 'success'
                    ? '<span class="label label-sm label-success">Success</span>'
                    : '<span class="label label-sm label-danger">Failed</span>';
            })
            ->editColumn('created_at', function ($record) {
                return $record->created_at ? '<a href="/settings/website-form-submission/' . $record->id . '">' . $record->created_at->format('d/m/Y H:i') : '';
            })
            ->editColumn('updated_at', function ($record) {
                return $record->updated_at ? $record->updated_at->format('d/m/Y H:i') : '';
            })
            ->rawColumns(['view', 'created_at', 'status', 'zoho_status'])
            ->make(true);
    }

    public function show($id)
    {
        $submission = WebsiteFormSubmission::findOrFail($id);

        return view('manage/settings/website-form-submission/view', [
            'submission' => $submission,
            'formLabel' => $this->formLabel($submission->form_key),
            'statusLabel' => $this->statusLabel($submission->status),
            'payloadJson' => $this->prettyJson($submission->payload),
            'zohoResponseJson' => $this->prettyJson($submission->zoho_response),
        ]);
    }

    protected function formOptions(): array
    {
        return ['request_designer_visit' => 'Request Designer Visit',];
    }

    protected function statusOptions(): array
    {
        return ['started' => 'Started', 'step1 complete' => 'Step 1 Complete', 'rejected' => 'Rejected', 'submitted before zoho' => 'Submitted Before Zoho', 'zoho created' => 'Zoho Created', 'zoho failed' => 'Zoho Failed',];
    }

    protected function formLabel(?string $formKey): string
    {
        return $this->formOptions()[$formKey] ?? ucwords(str_replace('_', ' ', (string)$formKey));
    }

    protected function statusLabel(?string $status): string
    {
        $label = $this->statusOptions()[$status] ?? ucwords(str_replace('_', ' ', (string)$status));

        return match ($status) {
            'zoho created' => '<span class="label label-sm label-success">' . e($label) . '</span>',
            'zoho failed' => '<span class="label label-sm label-danger">' . e($label) . '</span>',
            'rejected' => '<span class="label label-sm label-warning">' . e($label) . '</span>',
            'submitted before zoho' => '<span class="label label-sm label-info">' . e($label) . '</span>',
            'step1 complete' => '<span class="label label-sm label-primary">' . e($label) . '</span>',
            default => '<span class="label label-sm label-default">' . e($label) . '</span>',
        };
    }

    protected function prettyJson($value): string
    {
        if (empty($value)) {
            return '';
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

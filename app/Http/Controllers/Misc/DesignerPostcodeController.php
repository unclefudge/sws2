<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Misc\DesignerPostcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use nilsenj\Toastr\Facades\Toastr;
use Yajra\Datatables\Datatables;

class DesignerPostcodeController extends Controller
{

    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('edit.settings'))
            return view('errors/404');

        return view('manage/settings/designer-postcode/list', ['councils' => $this->councilOptions(),]);
    }

    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('edit.settings'))
            return view('errors/404');

        return view('manage/settings/designer-postcode/create', ['councils' => $this->councilOptions(),]);
    }

    public function store(Request $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('edit.settings'))
            return view('errors/404');

        $validated = $this->validatedData($request);

        DesignerPostcode::create($validated);

        Toastr::success('Created postcode');

        return redirect('/settings/designer-postcode');
    }

    public function edit($id)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('edit.settings'))
            return view('errors/404');

        $postcode = DesignerPostcode::findOrFail($id);

        return view('manage/settings/designer-postcode/edit', ['postcode' => $postcode, 'councils' => $this->councilOptions(),]);
    }

    public function update(Request $request, $id)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('edit.settings'))
            return view('errors/404');

        $postcode = DesignerPostcode::findOrFail($id);

        $validated = $this->validatedData($request);
        $postcode->update($validated);

        Toastr::success('Saved postcode');

        return redirect('/settings/designer-postcode');
    }

    protected function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'suburb' => ['required', 'string', 'max:120'],
            'postcode' => ['required', 'string', 'max:10'],
            'council' => ['nullable', 'string', Rule::in(array_keys($this->councilOptions()))],
            'active' => ['nullable', 'boolean'],
        ]);

        $validated['postcode'] = preg_replace('/\D+/', '', (string)$validated['postcode']);
        $validated['suburb'] = strtoupper(trim($validated['suburb']));
        $validated['state'] = 'NSW';
        $validated['active'] = $request->boolean('active');

        return $validated;
    }

    public function getPostcodes(Request $request)
    {
        $records = DesignerPostcode::query()->select(['id', 'suburb', 'postcode', 'state', 'council', 'active', 'updated_at',]);

        if ($request->filled('council')) {
            $records->where('council', $request->get('council'));
        }

        if ($request->filled('active') && $request->get('active') !== 'all') {
            $records->where('active', $request->get('active'));
        }

        return Datatables::of($records)
            ->addColumn('view', function ($record) {
                return '<a href="/settings/designer-postcode/' . $record->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
            })
            ->editColumn('active', function ($record) {
                return $record->active ? '<span class="label label-sm label-success">Active</span>' : '<span class="label label-sm label-default">Inactive</span>';
            })
            ->editColumn('updated_at', function ($record) {
                return $record->updated_at ? $record->updated_at->format('d/m/Y') : '';
            })
            ->rawColumns(['view', 'active'])
            ->make(true);
    }

    protected function councilOptions(): array
    {
        return [
            'Bayside' => 'Bayside',
            'City of Blacktown' => 'City of Blacktown',
            'Blue Mountains' => 'Blue Mountains',
            'Burwood' => 'Burwood',
            'Camden' => 'Camden',
            'Central Coast Council' => 'Central Coast Council',
            'City of Campbelltown' => 'City of Campbelltown',
            'Canada Bay' => 'Canada Bay',
            'Canterbury-Bankstown' => 'Canterbury-Bankstown',
            'Cumberland' => 'Cumberland',
            'City of Fairfield' => 'City of Fairfield',
            'Georges River' => 'Georges River',
            'Hawkesbury' => 'Hawkesbury',
            'Hornsby Shire' => 'Hornsby Shire',
            'Hunters Hill' => 'Hunters Hill',
            'Inner West' => 'Inner West',
            'Ku-Ring-Gai' => 'Ku-Ring-Gai',
            'Lane Cove' => 'Lane Cove',
            'City of Liverpool' => 'City of Liverpool',
            'Mosman' => 'Mosman',
            'North Sydney' => 'North Sydney',
            'Northern Beaches' => 'Northern Beaches',
            'City of Parramatta' => 'City of Parramatta',
            'City of Penrith' => 'City of Penrith',
            'Randwick' => 'Randwick',
            'City of Ryde' => 'City of Ryde',
            'Strathfield' => 'Strathfield',
            'Sutherland Shire' => 'Sutherland Shire',
            'City of Sydney' => 'City of Sydney',
            'The Hills Shire' => 'The Hills Shire',
            'Waverley' => 'Waverley',
            'Willoughby' => 'Willoughby',
            'Wingecarribee' => 'Wingecarribee',
            'Wollondilly' => 'Wollondilly',
            'Woollahra' => 'Woollahra',
            'Wyong' => 'Wyong',
        ];
    }
}

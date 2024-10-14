<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Misc\ConstructionDoc;
use DB;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;
use Yajra\Datatables\Datatables;


class ConstructionDocController extends Controller
{

    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('construction.doc'))
            return view('errors/404');

        $category_id = '';

        return view('construction/doc/list', compact('category_id'));
    }

    public function show($id)
    {
        //
    }

    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.construction.doc'))
            return view('errors/404');

        $category_id = request('category_id');

        return view('construction/doc/create', compact('category_id'));
    }


    public function edit($id)
    {
        $doc = ConstructionDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.construction.doc', $doc))
            return view('errors/404');

        return view('construction/doc/edit', compact('doc'));
    }


    public function destroy($id)
    {
        $doc = ConstructionDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('del.construction.doc', $doc))
            return json_encode("failed");

        // Delete attached file
        if (file_exists(public_path('/filebank/construction/doc/standards/' . $doc->attachment)))
            unlink(public_path('/filebank/construction/doc/standards/' . $doc->attachment));
        $doc->delete();

        return json_encode('success');
    }


    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.construction.doc'))
            return view('errors/404');

        $rules = ['name' => 'required', 'singlefile' => 'required'];
        $mesg = [
            'name.required' => 'The name field is required.',
            'categories.required' => 'The category field is required.',
            'singlefile.required' => 'The file field is required.',
        ];
        request()->validate($rules, $mesg); // Validate

        $doc_request = request()->all();
        $doc_request['category_id'] = 89; // Contract Standards defalt category
        //dd($doc_request);

        // Create Doc
        $doc = ConstructionDoc::create($doc_request);


        // Handle attached file
        if (request()->hasFile('singlefile')) {
            $file = request()->file('singlefile');

            $path = "filebank/construction/doc/standards";
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);
            $doc->attachment = $name;
            $doc->save();
        }
        Toastr::success("Created Doc");

        return view('construction/doc/list');
    }


    public function update($id)
    {
        $doc = ConstructionDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.construction.doc', $doc))
            return view('errors/404');

        $rules = ['name' => 'required'];
        $mesg = [
            'name.required' => 'The name field is required.',
            'categories.required' => 'The category field is required.',
        ];
        request()->validate($rules, $mesg); // Validate

        //dd(request()->all());
        $doc_request = request()->all();
        $doc->update($doc_request);

        ray('here');
        ray(request()->file('singlefile'));
        // Handle attached file
        if (request()->hasFile('singlefile')) {
            $file = request()->file('singlefile');
            ray($file);

            $path = "filebank/construction/doc/standards";
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());

            $orig_attachment = "$path/" . $doc->attachment;
            // Delete previous file
            if (file_exists(public_path($orig_attachment)))
                unlink(public_path($orig_attachment));

            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);
            $doc->attachment = $name;
            $doc->save();
        }
        Toastr::success("Updated Doc");

        return redirect('construction/doc/standards');
    }


    /**
     * Get Docs current user is authorised to manage + Process datatables ajax request.
     */
    public function getDocs()
    {
        $records = DB::table('construction_docs as d')
            ->select(['d.id', 'd.attachment', 'd.name', 'd.status'])
            ->where('d.status', '1');

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/filebank/construction/doc/standards/{{$attachment}}"><i class="fa fa-file-text-o"></i></a></div>')
            ->addColumn('action', function ($doc) {
                $record = ConstructionDoc::find($doc->id);
                $actions = '';
                if (Auth::user()->allowed2('edit.construction.doc', $record))
                    $actions .= '<a href="/construction/doc/' . $doc->id . '/edit' . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                if (Auth::user()->allowed2('del.construction.doc', $record))
                    $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/construction/doc/' . $doc->id . '" data-name="' . $doc->name . '"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->rawColumns(['id', 'action', 'hazdanger'])
            ->make(true);

        return $dt;
    }
}

<?php

namespace App\Http\Controllers\Misc\Form;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use File;
use Mail;
use Session;
use App\Models\Misc\Form\FormTemplate;
use App\Models\Misc\Form\Form;
use App\Models\Misc\Form\FormPage;
use App\Models\Misc\Form\FormSection;
use App\Models\Misc\Form\FormQuestion;
use App\Models\Misc\Form\FormResponse;
use App\Models\Misc\Form\FormLogic;
use App\Models\Misc\Form\FormNote;
use App\Models\Misc\Form\FormFile;
use App\Models\Misc\TemporaryFile;
use App\Models\Comms\Todo;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Intervention\Image\Facades\Image;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class FormController
 * @package App\Http\Controllers
 */
class FormController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->hasAnyPermissionType('site.scaffold.handover'))
        //    return view('errors/404');

        return view('site/inspection/custom/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.site.scaffold.handover'))
        //    return view('errors/404');

        return view('misc/form/create', compact('site'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $form = Form::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('view.site.scaffold.handover', $form))
        //    return view('errors/404');

        return redirect("/form/$id/1");
        //return view('/site/inspection/custom/show', compact('form'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showPage($id, $pagenumber)
    {
        $form = Form::findOrFail($id);
        $page = FormPage::where('template_id', $form->template->id)->where('order', $pagenumber)->first();

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('view.site.scaffold.handover', $form))
        //    return view('errors/404');

        // Select 2 question ids
        $s2_ids = FormQuestion::where('template_id', $form->template->id)->where('type', 'select')->where('status', 1)->where('type_version', 'select2')->pluck('id')->toArray();
        $s2_phs = FormQuestion::where('template_id', $form->template->id)->where('type', 'select')->where('status', 1)->pluck('placeholder', 'id')->toArray();

        $formlogic = FormLogic::where('template_id', $form->template->id)->where('page_id', $page->id)->where('status', 1)->get();

        // Check is Show Required fields is set 'Form Submitted' field is only valid for same day otherwise reset null
        $showrequired = 0;
        $failed_questions = null;
        $today = Carbon::now()->format('Ymd');
        if ($form->submitted) {
            if ($form->submitted->format('Ymd') == $today) {
                $showrequired = 1;
                $failed_ids = $this->verifyFormCompleted($form);
                $failed_questions = FormQuestion::find($failed_ids);
            } else {
                $form->submitted = null;
                $form->save();
                $showrequired = 0;
            }
        }

        // Get Page data
        return view('/site/inspection/custom/show', compact('form', 'pagenumber', 'formlogic', 's2_ids', 's2_phs', 'showrequired', 'failed_questions'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $form = Form::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('edit.site.scaffold.handover', $report))
        //    return view('errors/404');

        return view("/misc/form/edit", compact('form'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.site.scaffold.handover'))
        //    return view('errors/404');

        //return redirect('/misc/form/' . $form->id . /edit);
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $form = Form::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('edit.site.scaffold.handover', $report))
        //    return view('errors/404');

        $nextpage = request('nextpage');
        $questions_asked = [];
        $questions_media = [];
        $debug = true;

        // Form has been re-opened
        if (request('status'))
            $form->status = request('status');

        //dd(request()->all());

        // Loop through ALL form questions
        foreach ($form->questions as $question) {
            $qid = $question->id;

            // Only update questions for current page
            if ($question->section->page->order == request('page')) {
                $questions_asked[] = $qid;
                $responses_given = [];

                //
                // Question Responses
                //  - convert response to an array (Process Single + Multiple response with same code)
                $resp_array = [];
                if (request()->has("q$qid")) //ie. request variable exists
                    $resp_array = (is_array(request("q$qid"))) ? request("q$qid") : [request("q$qid")];

                foreach ($resp_array as $resp) {
                    if ($resp || $resp == '0') { // Response not blank/null
                        if ($debug) echo "Q:$qid Val:$resp T:$question->type<br>";
                        if ($question->type_special == 'site') $form->site_id = $resp;  // Add the Site ID to form

                        // Set option_id + date field if required
                        $option_id = ($question->type == 'select' && !in_array($question->type_special, ['site', 'user'])) ? $resp : null;  // set option_id for select questions
                        $date = ($question->type == 'datetime') ? $date = Carbon::createFromFormat('d/m/Y H:i', $resp)->toDateTimeString() : null;  // set date for datetime questions
                        //$item_request['date'] = Carbon::createFromFormat('d/m/Y H:i', request('date') . '00:00')->toDateTimeString();  date (no time)

                        $response = FormResponse::where('form_id', $form->id)->where('question_id', $qid)->where('value', $resp)->first();
                        if ($response) {
                            $response->value = $resp;
                            $response->option_id = $option_id;
                            $response->date = $date;
                            $response->save();
                        } else
                            $response = FormResponse::create(['form_id' => $form->id, 'question_id' => $qid, 'value' => $resp, 'option_id' => $option_id, 'date' => $date]);
                        $responses_given[] = $response->id;
                    }
                }
                // Delete responses excluding non blank/null responses given if Form 'active'
                if ($form->status)
                    $delete_blank_responses = FormResponse::where('form_id', $form->id)->where('question_id', $qid)->whereNotIn('id', $responses_given)->delete();

                //
                // Question Notes
                //
                $question_notes = request("q$qid-notes");
                if ($question_notes) {
                    $note = FormNote::where('form_id', $form->id)->where('question_id', $qid)->first();
                    if ($note) {
                        $note->notes = $question_notes;
                        $note->save();
                    } else
                        $note = FormNote::create(['form_id' => $form->id, 'question_id' => $qid, 'notes' => $question_notes]);
                } else
                    $note = FormNote::where('form_id', $form->id)->where('question_id', $qid)->delete(); // delete existing note if exists


                //
                // Question Media
                //
                $question_media = request("q$qid-media");
                if ($question_media) {
                    foreach ($question_media as $tmp_filename) {
                        $tempFile = TemporaryFile::where('folder', $tmp_filename)->first();
                        if ($tempFile) {
                            // Move temp file to forms directory
                            $form_dir = "/filebank/form/$form->id";
                            if (!is_dir(public_path($form_dir))) mkdir(public_path($form_dir), 0777, true);  // Create directory if required

                            $tempFilePublicPath = public_path($tempFile->folder) . "/" . $tempFile->filename;
                            if (file_exists($tempFilePublicPath)) {
                                $newFile = "$form_dir/" . $question->id . '-' . $tempFile->filename;
                                rename($tempFilePublicPath, public_path($newFile));
                                $form_file = FormFile::create(['form_id' => $form->id, 'question_id' => $question->id, 'type' => 'photo', 'attachment' => $newFile]);
                                $response = FormResponse::where('form_id', $form->id)->where('question_id', $qid)->where('value', $form_file)->first();
                                if (!$response)
                                    $response = FormResponse::create(['form_id' => $form->id, 'question_id' => $qid, 'value' => $form_file->id, 'option_id' => null, 'date' => null]);
                                $responses_given[] = $response->id;
                            }

                            // Delete Temporary file directory + record
                            $tempFile->delete();
                            rmdir(public_path($tempFile->folder));
                        }
                    }
                }
            }
        }

        //
        // Delete any media
        //
        if (request('myGalleryDelete')) {
            foreach (request('myGalleryDelete') as $filename) {
                list($qid, $rest) = explode('-', $filename, 2);

                // Delete FormFile + FormResponses
                $form_file = FormFile::where('form_id', $form->id)->where('question_id', $qid)->where('attachment', "/filebank/form/$form->id/$filename")->first();
                if ($form_file) {
                    FormResponse::where('form_id', $form->id)->where('question_id', $qid)->where('value', $form_file->id)->delete();
                    $form_file->delete();
                }
            }
        }

        $form->save();

        // Final Page / Complete Form submitted
        if ($nextpage == 'complete') {
            $failed_questions = $this->verifyFormCompleted($form);
            if ($debug) var_dump($failed_questions);

            //dd($failed_questions);

            $form->submitted = Carbon::now()->toDateTimeString();
            if ($failed_questions) {
                $first_failed = FormQuestion::find(reset($failed_questions)); // get first element of array failed_questions
                $nextpage = ($first_failed) ? $first_failed->section->page->order : $nextpage;
            } else {
                $form->submitted = null;
                $form->completed = Carbon::now()->toDateTimeString();
                $form->status = 0;
                $nextpage = 1;
            }
            $form->save();
        }

        //dd(request()->all());

        // Create Action for question - redirect to ToDoo
        if (request('addAction'))
            return redirect("todo/create/form/$form->id-" . request('addAction'));
        elseif (request('showAction'))
            return redirect("todo/" . request('showAction'));

        return redirect("form/$form->id/$nextpage");
    }

    public function verifyFormCompleted($form)
    {
        $debug = false;
        // Verify all required questions are completed.
        if ($debug) echo " <br>Form Completed - Verify Required Fields <br>--------------------------------------------------</br> ";
        $required_questions = [];
        $failed_questions = [];
        $logic_questions = [];
        $delete_responses = [];
        foreach ($form->questions as $question) {
            if ($question->required) {
                $response = FormResponse::where('form_id', $form->id)->where('question_id', $question->id)->first();
                $val = ($response) ? $response->value : '';

                // Convert $val to 'zero' in the cases it's '0' for checking if valid response
                $val = ($val == '0') ? '{zero}' : $val;

                // Media Only Question - check if media found
                if ($question->type == 'media')
                    $val = ($question->files($form->id)->count()) ? 'media found' : null;

                // Check if question is affected by any logic
                $affectedByLogic = $question->affectedByLogic();
                if ($affectedByLogic->count() == 0) {
                    // Standard Question not affected by any logic
                    $required_questions[] = $question->id;
                    if (!$val)
                        $failed_questions[] = $question->id; // Questions has non blank/null response ie FAILS required check
                } else {
                    // Question is affected by logic
                    foreach ($affectedByLogic as $logic) {
                        // Get Source Question response values
                        $sourceQuestion = FormQuestion::find($logic->question_id);
                        $sourceResponseArray = $sourceQuestion->response($form->id)->pluck('value')->toArray();
                        //$sourceResponseString = implode(',', $sourceResponseArray);

                        $logic_questions[$question->id][$logic->id] = "<br> ===LOGIC[$logic->id] if (Q:$logic->question_id $logic->match_operation $logic->match_value) then Trigger:$logic->trigger[$logic->trigger_id] <br>";

                        if ($logic->trigger == 'question' || true) {
                            $match_array = explode(',', $logic->match_value);

                            // Loop through each Logic Required Question/Section IDs (match_array) and determine if valid response exists
                            foreach ($match_array as $match_val) {
                                if (in_array($match_val, $sourceResponseArray)) {
                                    $required_questions[] = $question->id;
                                    if (!$val) {
                                        $failed_questions[] = $question->id;
                                    }
                                    break;
                                } else {
                                    // Delete question from Required+Failed Questions as Question must match ALL logic
                                    //  - this occures when single question has multiple logic statements eg Template 1, Q48
                                    if (($key = array_search($question->id, $required_questions)) !== false)
                                        unset($required_questions[$key]);

                                    if (($key = array_search($question->id, $failed_questions)) !== false)
                                        unset($failed_questions[$key]);
                                }
                            }
                        }
                    }

                    // If question is affected by logic but a) has value + b) now not required then delete the response
                    if ($val && !in_array($question->id, $required_questions))
                        $delete_responses[] = $question->id;

                } // End question is affected by logic

                //
                // Debug statements
                //
                if ($debug) {
                    $fail = (in_array($question->id, $failed_questions)) ? " * " : '';
                    $del = (in_array($question->id, $delete_responses)) ? "DELETE" : '';
                    $req = '';
                    $logic_mesg = '';

                    // Check if question has an logic from LogicArray and if so then match to current question
                    //  - a single question can be affected by multiple logic operations
                    if (array_key_exists($question->id, $logic_questions)) {
                        foreach ($logic_questions as $qid => $logic_array) {
                            if ($qid == $question->id) {
                                $req = (in_array($question->id, $required_questions)) ? " REQUIRED" : '';
                                foreach ($logic_array as $logic_id => $mesg)
                                    $logic_mesg .= $mesg;
                            }
                        }
                    }
                    echo "$fail Q:$question->id Page:$question->page_id Sect:$question->section_id == [$val] $req $del $logic_mesg <br>";
                }
            } // end required question
        }

        // Remove duplicates - these can occur when a question is affected by multiple logic statements eg Template 1, Q48
        $required_questions = array_unique($required_questions);

        // Debug values
        if ($debug) {
            echo " <br>Required Questions<br>";
            var_dump($required_questions);
            echo " <br>Logic Questions <br>";
            var_dump($logic_questions);
            echo " <br>Failed Questions <br>";
            var_dump($failed_questions);
            echo " <br>Delete Questions <br>";
            var_dump($delete_responses);
        }

        // Delete Non Required empty/blank questions
        if (count($delete_responses)) {
            //$array1 = FormResponse::where('form_id', $form->id)->whereNotIn('question_id', $required_questions)->pluck('id')->toArray();
            //$array2 = FormResponse::where('form_id', $form->id)->whereIn('question_id', $delete_responses)->pluck('id')->toArray();
            //var_dump($array1);
            //var_dump($array2);
            $delete_non_required = FormResponse::where('form_id', $form->id)->wherein('question_id', $delete_responses)->delete();
        }

        return $failed_questions;
    }


    /**
     * Upload Filepond file
     */
    public function upload()
    {
        $folder = '';
        $company_id = (Auth::check()) ? Auth::user()->company->reportsTo()->id : '3';
        $path = "filebank/tmp/$company_id/upload";

        $files = request()->allFiles();
        if ($files) {
            // FilePond only uploads 1 file at a time (even with multiple) so if array exists then it only has 1 element
            // - get array key which is the ID of the input ie q1
            $firstKey = array_key_first($files);

            if (request()->hasFile($firstKey)) {
                // As input variable is an array (ie. q1[]) loop through array to save each file
                // - Filepond will only have 1 element in array but array is required to save the uploaded TemporaryFiles to actual Form on (update/save)
                foreach (request()->file($firstKey) as $file) {
                    $filename = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
                    $folder = "$path/" . uniqid() . '-' . now()->timestamp; // create unique folder for tmp file
                    $path_name = $folder . '/' . $filename;
                    $file->move($folder, $filename);

                    // resize the image to a width of 1024 and constrain aspect ratio (auto height)
                    if (exif_imagetype($path_name)) {
                        Image::make(url($path_name))
                            ->resize(1024, null, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            })
                            ->save($path_name);
                    }

                    // Store temporary file to DB
                    $tempFile = TemporaryFile::create(['folder' => $folder, 'filename' => $filename, 'company_id' => $company_id]);
                }
            }
        }

        return $folder;
    }

    /**
     * Save attached Media to existing Issue
     */
    public function saveAttachedMedia($file)
    {
        $site = Site::findOrFail($this->site_id);
        $path = "filebank/site/" . $site->id . '/hazard';
        $name = 'hazard-' . $site->code . '-' . $this->id . '-' . Auth::user()->id . '-1.' . strtolower($file->getClientOriginalExtension());

        // Ensure filename is unique by adding counter to similiar filenames
        $count = 2;
        while (file_exists(public_path("$path/$name")))
            $name = 'hazard-' . $site->code . '-' . $this->id . '-' . Auth::user()->id . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());

        $path_name = $path . '/' . $name;
        $file->move($path, $name);

        // resize the image to a width of 1024 and constrain aspect ratio (auto height)
        if (exif_imagetype($path_name)) {
            Image::make(url($path_name))
                ->resize(1024, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($path_name);
        } else
            Toastr::error("Bad image");

        $this->attachment = $name;
        $this->save();
    }

    /**
     * Upload Filepond file
     */
    public function deleteUpload()
    {
        // Required to remove temporary uploaded Filepond file
        //dd(request()->all());
        return 'delete upload';
    }



    /**
     * Get Custom Form  - vue3
     */
    /*
    public function getForm($id)
    {

        $form = Form::findOrFail($id);
        $template = FormTemplate::findOrFail($form->template_id);
        //$pages = $template->pages;
        //return ($pages);

        $pages = [];


        // Create Template Object
        $template_obj = new \stdClass();
        $template_obj->id = $template->id;
        $template_obj->name = $template->name;
        $template_obj->description = $template->description;

        // Add Pages
        $template_obj->pages = [];
        foreach ($template->pages as $page) {
            // Create page Object
            $page_obj = new \stdClass();
            $page_obj->id = $page->id;
            $page_obj->name = $page->name;
            $page_obj->description = $page->description;
            $page_obj->order = $page->order;

            // Add Sections
            $page_obj->sections = [];
            foreach ($page->sections as $section) {
                // Create Section Object
                $section_obj = new \stdClass();
                $section_obj->id = $section->id;
                $section_obj->name = $section->name;
                $section_obj->description = $section->description;
                $section_obj->order = $section->order;

                // Add Questions
                $section_obj->questions = [];
                foreach ($section->questions as $question) {
                    // Create Question Object
                    $question_obj = new \stdClass();
                    $question_obj->id = $question->id;
                    $question_obj->name = $question->name;
                    $question_obj->type = $question->type;

                    // Get Response if exists
                    $response = FormResponse::where('form_id', $form->id)->where('question_id', $question->id)->where('status', 1)->first();
                    if ($response) {
                        $question_obj->response_value = $response->value;
                        $question_obj->response_option = $response->option_id;
                    } else {
                        $question_obj->response_value = null;
                        $question_obj->response_option = null;
                    }

                    $question_obj->type_special = $question->type_special;
                    $question_obj->type_version = $question->type_version;
                    $question_obj->order = $question->order;
                    $question_obj->default = $question->default;
                    $question_obj->multiple = $question->multiple;
                    $question_obj->required = $question->required;
                    $question_obj->placeholder = $question->placeholder;
                    $question_obj->helper = $question->helper;
                    $question_obj->width = $question->width;


                    // Add Question Object to Section
                    $section_obj->questions[] = $question_obj;
                }
                // Add Section Object to Page
                $page_obj->sections[] = $section_obj;
            }
            // Add Page Object to Template
            $template_obj->pages[] = $page_obj;
        }

        // Get Form Responses
        $responses = $form->responses;


        $json[] = $template_obj;
        $json[] = $responses;


        //$json = [];
        //$json[] = $template;
        //$json[] = $pages;
        //$json[] = $template->sections;

        return $json;
    } */

    /**
     * Save the Custom Form.  Vue3 version
     */
    /*
    public function saveForm()
    {
        //dd(request()->all());
        $custom_form = request('custom_form');
        if ($custom_form) {
            $template = FormTemplate::findOrFail($custom_form['id']);

            if ($template) {
                // Update Template
                $template->name = $custom_form['name'];
                $template->description = $custom_form['description'];
                $template->save();

                // Save Pages
                $pages = $custom_form['pages'];
                for ($i = 0; $i < count($pages); $i ++) {
                    if ($pages[$i]['id'] != 'new') {
                        $page = FormPage::findOrFail($pages[$i]['id']);
                        if ($page) {
                            // Update existing Page
                            $page->name = $pages[$i]['name'];
                            $page->description = $pages[$i]['description'];
                            $page->order = $pages[$i]['order'];
                            $page->save();
                        }
                    } else {
                        // Create new Page
                    }

                    // Save Sections
                    $sections = $pages[$i]['sections'];
                    for ($i = 0; $i < count($sections); $i ++) {
                        if ($sections[$i]['id'] != 'new') {
                            $section = FormSection::findOrFail($sections[$i]['id']);
                            if ($section) {
                                // Update existing Section
                                $section->page_id = $page->id;
                                $section->name = $sections[$i]['name'];
                                $section->description = $sections[$i]['description'];
                                $section->order = $sections[$i]['order'];
                                $section->save();
                            }
                        } else {
                            // Create new Section
                        }
                    }
                }
            }
        }

        Toastr::success("Template saved");

        return response()->json(['status' => 'ok', 'success' => true,], 200);
        //return response()->json(['status'  => 'error', 'success' => false, 'message' => 'Invalid email'], 406);
        //return response()->json(['success' => true, 'message' => 'Your AJAX processed correctly']);
    }*/


    /**
     * Get Templates current user is authorised to manage + Process datatables ajax request.
     */
    public function getSafetyDesignForms()
    {
        $records = Form::select([
            'forms.id', 'forms.template_id', 'forms.site_id', 'forms.name', 'forms.company_id', 'forms.status', 'forms.updated_at', 'forms.created_at',
            DB::raw('DATE_FORMAT(forms.created_at, " % d /%m /%y") AS createddate'),
            DB::raw('DATE_FORMAT(forms.updated_at, " % d /%m /%y") AS updateddate'),
            DB::raw('sites.name AS sitename')])
            ->join('sites', 'forms.site_id', '=', 'sites.id')
            ->where('forms.template_id', 1)
            ->where('forms.company_id', Auth::user()->company_id)
            ->where('forms.status', 1);

        $dt = Datatables::of($records)
            ->addColumn('view', function ($report) {
                return ('<div class="text - center"><a href=" / site / inspection / custom / ' . $report->id . '"><i class="fa fa - search"></i></a></div>');
            })
            ->addColumn('action', function ($rec) {
                return '<a href=" / site / inspection / custom / ' . $rec->id . ' / edit" class="btn blue btn - xs btn - outline sbold uppercase margin - bottom"><i class="fa fa - pencil"></i> Edit</a>';
            })
            ->rawColumns(['view', 'name', 'updated_at', 'created_at', 'action'])
            ->make(true);

        return $dt;
    }
}

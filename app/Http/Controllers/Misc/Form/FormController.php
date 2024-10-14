<?php

namespace App\Http\Controllers\Misc\Form;

use App\Http\Controllers\Controller;
use App\Models\Misc\Action;
use App\Models\Misc\Form\Form;
use App\Models\Misc\Form\FormFile;
use App\Models\Misc\Form\FormLogic;
use App\Models\Misc\Form\FormNote;
use App\Models\Misc\Form\FormOption;
use App\Models\Misc\Form\FormPage;
use App\Models\Misc\Form\FormQuestion;
use App\Models\Misc\Form\FormResponse;
use App\Models\Misc\Form\FormSection;
use App\Models\Misc\Form\FormTemplate;
use App\Models\Misc\TemporaryFile;
use App\Models\Site\Site;
use App\Models\Site\SiteHazard;
use App\User;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Mail;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class FormController
 * @package App\Http\Controllers
 */
class FormController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.inspection.whs'))
            return view('errors/404');

        return view('site/inspection/custom/list');
    }

    public function listForms($template_id)
    {
        $template = FormTemplate::find($template_id);

        // Only allow master templates
        if ($template->parent_id)
            return view('errors/404');

        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.inspection.whs'))
            return view('errors/404');

        return view('site/inspection/custom/list2', compact('template'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createForm($template_id)
    {
        $template = FormTemplate::find($template_id);

        // Only allow master templates
        if ($template->parent_id)
            return view('errors/404');

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.inspection.whs'))
            return view('errors/404');

        // Create New Form based on Master template with Current revision (current_id)
        $form = Form::create(['template_id' => $template->current_id, 'company_id' => Auth::user()->company->reportsTo()->id]);

        return redirect("/site/inspection/$form->id/1");
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
        if (!Auth::user()->allowed2('view.site.inspection.whs', $form))
            return view('errors/404');

        return redirect("/site/inspection/$id/1");
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showPage($id, $pagenumber)
    {
        $form = Form::findOrFail($id);
        $page = FormPage::where('template_id', $form->template_id)->where('order', $pagenumber)->first();

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.inspection.whs', $form))
            return view('errors/404');

        // Select 2 question ids
        $s2_ids = FormQuestion::where('template_id', $form->template_id)->where('type', 'select')->where('status', 1)->where('type_version', 'select2')->pluck('id')->toArray();
        $s2_phs = FormQuestion::where('template_id', $form->template_id)->where('type', 'select')->where('status', 1)->pluck('placeholder', 'id')->toArray();

        $formlogic = FormLogic::where('template_id', $form->template_id)->where('page_id', $page->id)->where('status', 1)->get();

        // Check is Show Required fields is set 'Form Submitted' field is only valid for same day otherwise reset null
        $showrequired = 0;
        $failed_questions = null;
        $today = Carbon::now()->format('Ymd');
        if ($form->submitted_at) {
            if ($form->submitted_at->format('Ymd') == $today) {
                $showrequired = 1;
                $failed_ids = $this->verifyFormCompleted($form);
                $failed_questions = FormQuestion::find($failed_ids);
            } else {
                $form->submitted_at = null;
                $form->save();
                $showrequired = 0;
            }
        }


        //dd($page);
        //dd($formlogic);
        //dd($sections);

        $sections = FormSection::where('page_id', $page->id)->whereNull('parent')->orderBy('order')->get();


        //dd($sections);
        //dd('here');

        // Get Page data
        return view('/site/inspection/custom/show', compact('form', 'page', 'sections', 'pagenumber', 'formlogic', 's2_ids', 's2_phs', 'showrequired', 'failed_questions'));
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
        foreach ($form->questions() as $question) {
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
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.site.inspection.whs'))
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
        if (!Auth::user()->allowed2('edit.site.inspection.whs', $form))
            return view('errors/404');

        $nextpage = request('nextpage');
        $questions_asked = [];
        $questions_media = [];
        $debug = false;

        //dd(request()->all());

        // Form has been re-opened
        $form_reopened = (request('status') == '1' && $form->status == '0') ? true : false;

        if (request('status'))
            $form->status = request('status');

        // Loop through ALL form questions
        foreach ($form->questions() as $question) {
            $qid = $question->id;

            // Only update questions for current page
            if ($question->section->page->order == request('page')) {

                // Only update Form Responses if Form was just re-opened
                //   - because no input fields will have values
                if (!$form_reopened) {
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
                            // Add the Site details to form
                            if ($question->type_special == 'site') {
                                $site = Site::find($resp);
                                $form->site_id = $resp;
                                $form->site_name = $site->name;
                            }
                            // Add the Inspected At details to form
                            if ($question->name == 'Inspected at') {
                                $form->inspected_at = Carbon::createFromFormat('d/m/Y H:i', $resp)->toDateTimeString();
                            }
                            // Add the Inspected By details to form
                            if ($question->name == 'Inspected by') {
                                $user = User::find($resp);
                                $form->inspected_by = $resp;
                                $form->inspected_by_name = $user->fullname;
                            }

                            // Set option_id + date field if required
                            $option_id = ($question->type == 'select' && !in_array($question->type_special, ['site', 'user'])) ? $resp : null;  // set option_id for select questions
                            $date = null;
                            if ($question->type == 'datetime')
                                $date = Carbon::createFromFormat('d/m/Y H:i', $resp)->toDateTimeString();  // set date for datetime questions
                            if ($question->type == 'date')
                                $date = Carbon::createFromFormat('d/m/Y H:i:s', $resp . ' 00:00:00')->toDateTimeString();  // set date (not time) for datetime questions

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
                }

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
                            $form_dir = "/filebank/inspection/$form->id";
                            if (!is_dir(public_path($form_dir))) mkdir(public_path($form_dir), 0777, true);  // Create directory if required

                            $tempFilePublicPath = public_path($tempFile->folder) . "/" . $tempFile->filename;
                            if (file_exists($tempFilePublicPath)) {
                                $newFile = "$form_dir/" . $question->id . '-' . $tempFile->filename;
                                rename($tempFilePublicPath, public_path($newFile));
                                $filename = pathinfo($tempFile->filename, PATHINFO_BASENAME);

                                // Determine file extension and set type
                                $ext = pathinfo($tempFile->filename, PATHINFO_EXTENSION);
                                $filename = pathinfo($tempFile->filename, PATHINFO_BASENAME);
                                $type = (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'])) ? 'image' : 'file';

                                $form_file = FormFile::create(['form_id' => $form->id, 'question_id' => $question->id, 'type' => $type, 'name' => $filename, 'attachment' => $newFile]);
                                $response = FormResponse::where('form_id', $form->id)->where('question_id', $qid)->where('value', $form_file)->first();
                                if (!$response)
                                    $response = FormResponse::create(['form_id' => $form->id, 'question_id' => $qid, 'value' => $form_file->id, 'option_id' => null, 'date' => null]);
                                $responses_given[] = $response->id;
                            }

                            // Delete Temporary file directory + record
                            $tempFile->delete();
                            $files = scandir($tempFile->folder);
                            if (count($files) == 0)
                                rmdir(public_path($tempFile->folder));
                        }
                    }
                }
            }
        }

        //
        // Delete any media marked to delete
        //
        if (request('myGalleryDelete')) {
            foreach (request('myGalleryDelete') as $filename) {
                list($qid, $rest) = explode('-', $filename, 2);

                // Delete FormFile + FormResponses
                $form_file = FormFile::where('form_id', $form->id)->where('question_id', $qid)->where('attachment', "/filebank/inspection/$form->id/$filename")->first();
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

            $form->submitted_at = Carbon::now()->toDateTimeString();
            if ($failed_questions) {
                $first_failed = FormQuestion::find(reset($failed_questions)); // get first element of array failed_questions
                $nextpage = ($first_failed) ? $first_failed->section->page->order : $nextpage;
            } else {
                $form->submitted_at = null;
                $form->completed_at = Carbon::now()->toDateTimeString();
                $form->status = 0;
                $nextpage = 1;

                // Perform Complete Form Sumbitted Actions
                //if ($form->template->parent_id == 3)  // construction WHS inspection
                //    $this->completedActionsConstructionWHS($form->id);
            }
            $form->save();
        }

        //dd(request()->all());

        // Create Action for question - redirect to ToDoo
        if (request('addAction'))
            return redirect("todo/create/inspection/$form->id-" . request('addAction'));
        elseif (request('showAction'))
            return redirect("todo/" . request('showAction'));

        return redirect("site/inspection/$form->id/$nextpage");
    }

    public function completedActionsConstructionWHS($form_id)
    {
        $form = Form::findOrFail($form_id);

        $formoption2 = FormOption::find(2);
        $formoption3 = FormOption::find(3);

        foreach ($form->questions() as $question) {
            if ($question->required) {
                $response = FormResponse::where('form_id', $form->id)->where('question_id', $question->id)->first();
                $val = ($response) ? $response->value : '';

                // Convert $val to 'zero' in the cases it's '0' for checking if valid response
                $val = ($val == '0') ? '{zero}' : $val;

                // For all CONN questions
                if ($question->type == 'select' && $question->type_special == 'CONN') {


                    // Create Site Hazard for Non-Compliant or Improvement responses
                    if (in_array($val, [2, 3])) {

                        $option = ($val == 2) ? $formoption2 : $formoption3;
                        $reason = "$option->value: $question->name";
                        $action_req = ($val == 2) ? '0' : '1';


                        $haz_request = ['site_id' => $form->site_id, 'reason' => $reason, 'action_required' => $action_req, 'source' => 'WHS Inspection'];

                        echo "Creating Hazard for site($form->site_id) : $reason<br>";

                        $hazard = SiteHazard::create($haz_request);

                        // Create action + add attachments
                        if ($hazard) {
                            $action = Action::create(['action' => 'Reported Hazard', 'table' => 'site_hazards', 'table_id' => $hazard->id]);
                            $hazard->touch(); // update timestamp

                            // Copy any attachments
                            if ($question->files($form->id)) {
                                foreach ($question->files($form->id) as $file) {
                                    $hazard->saveCopyAttachment($file->attachment);
                                    echo "copy file: $file->attachment<br>";
                                }
                            }

                            // Email hazard
                            $hazard->emailHazard($action);
                        }
                    }
                }
            }
        }
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

                    // resize the image so that the largest side fits within the limit; the smaller
                    // side will be scaled to maintain the original aspect ratio
                    if (exif_imagetype($path_name)) {
                        Image::make(url($path_name))
                            ->resize(1024, 1024, function ($constraint) {
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
     * Upload Filepond file
     */
    public function deleteUpload()
    {
        // Required to remove temporary uploaded Filepond file
        //dd(request()->all());
        return 'delete upload';
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $form = Form::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("del.site.inspection.whs", $form))
            return json_encode("failed");

        // Delete any Form Attachments + remove directory
        $dir = public_path("/filebank/inspection/$id");
        if (is_dir($dir)) {
            array_map('unlink', glob("$dir/*.*"));
            rmdir($dir);
        }

        // Delete associated Form records
        Form::where('id', $id)->delete();
        FormFile::where('form_id', $id)->delete();
        FormNote::where('form_id', $id)->delete();
        FormResponse::where('form_id', $id)->delete();

        return json_encode('success');
    }


    /**
     * Get Templates current user is authorised to manage + Process datatables ajax request.
     */
    public function getForms()
    {
        $template_ids = FormTemplate::where('parent_id', request('template_id'))->pluck('id')->toArray();;

        $records = Form::select([
            'forms.id', 'forms.template_id', 'forms.site_name', 'forms.inspected_by_name', 'forms.inspected_at', 'forms.company_id', 'forms.status', 'forms.updated_at', 'forms.created_at', 'forms.created_by',
            DB::raw('DATE_FORMAT(forms.created_at, "%d/%m/%y") AS createddate'),
            DB::raw('DATE_FORMAT(forms.inspected_at, "%d/%m/%y") AS inspecteddate'),
            DB::raw('DATE_FORMAT(forms.completed_at, "%d/%m/%y") AS completeddate')])
            ->whereIn('forms.template_id', $template_ids)
            ->where('forms.company_id', Auth::user()->company->reportsTo()->id)
            ->where('forms.status', request('status'));

        //$records = Form::where('template_id',request('template_id'))->where('company_id', Auth::user()->company->reportsTo()->id)->where('forms.status', request('status'));

        $dt = Datatables::of($records)
            ->addColumn('view', function ($report) {
                return ('<div class="text-center"><a href="/site/inspection/' . $report->id . '"><i class="fa fa-search"></i></a></div>');
            })
            ->addColumn('created_by', function ($report) {
                $user = User::find($report->created_by);
                return ($user) ? $user->fullname : '';
            })
            ->addColumn('action', function ($report) {
                $actions = '';
                if (Auth::user()->allowed2("del.site.inspection.whs", $report))
                    $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/site/inspection/' . $report->id . '" data-name="' . $report->site_name . '"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->rawColumns(['view', 'name', 'created_at', 'updated_at', 'created_at', 'action'])
            ->make(true);

        return $dt;
    }
}

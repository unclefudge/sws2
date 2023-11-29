<?php

namespace App\Http\Controllers\Misc\Form;

use App\Http\Controllers\Misc\FilesystemIterator;
use App\Http\Controllers\Misc\RecursiveDirectoryIterator;
use App\Http\Controllers\Misc\RecursiveIteratorIterator;
use App\Http\Controllers\Misc\Response;
use DB;
use PDF;
use Mail;
use Session;
use App\User;
use App\Models\Company\Company;
use App\Models\Site\SiteQaAction;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Models\Misc\Category;
use App\Models\Misc\Permission2;
use App\Models\Misc\Action;
use App\Models\Misc\Form\Form;
use App\Models\Misc\Form\FormTemplate;
use App\Models\Misc\Form\FormPage;
use App\Models\Misc\Form\FormSection;
use App\Models\Misc\Form\FormQuestion;
use App\Models\Misc\Form\FormOption;
use App\Models\Misc\Form\FormLogic;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

class FormSetupController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {

    }

    /*
     * Reset Template Form
     */
    public function resetFormTemplate()
    {
        $now = Carbon::now()->format('d/m/Y g:i a');
        echo "<b>Reseting Sample Form Template - $now</b></br>";
        DB::table('forms_templates')->truncate();
        DB::table('forms_pages')->truncate();
        DB::table('forms_sections')->truncate();
        DB::table('forms_questions')->truncate();
        DB::table('forms_options')->truncate();
        DB::table('forms_logic')->truncate();
        DB::table('forms')->truncate();
        DB::table('forms_responses')->truncate();
        DB::table('forms_files')->truncate();
        DB::table('forms_actions')->truncate();

        //
        // Creating special options
        //
        echo "Creating Special Option</br>";
        // CONN
        FormOption::create(['text' => 'Compliant', 'value' => 'Compliant', 'order' => 1, 'colour' => 'green', 'score' => 2, 'group' => 'CONN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'Observation', 'value' => 'Observation', 'order' => 2, 'colour' => 'orange', 'score' => 1, 'group' => 'CONN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'Non-conformance', 'value' => 'Non-conformance', 'order' => 3, 'colour' => 'red', 'score' => - 2, 'group' => 'CONN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'Not Applicable', 'value' => 'Not Applicable', 'order' => 4, 'colour' => null, 'score' => 0, 'group' => 'CONN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // YN
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 1, 'colour' => null, 'group' => 'YN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => 'YN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // YrN
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 1, 'colour' => 'red', 'group' => 'YrN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => 'YrN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // YgN
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 1, 'colour' => 'green', 'group' => 'YgN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => 'YgN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // YNNA
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => 'YNNA', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => 'YNNA', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'N/A', 'value' => 'N/A', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => 'YNNA', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

    }

    //
    // Show FormTemplate
    //
    public function showTemplate($id)
    {
        $template = FormTemplate::find($id);

        echo "Name: $template->name ($template->description)<br>";
        echo "P:" . $template->pages->count() . " S:" . $template->sections->count() . " Q:" . $template->questions->count() . "<br>-----------<br><br>";

        foreach ($template->pages as $page) {
            echo "<br>=====================================<br>Page $page->id : $page->name<br>=====================================<br>";
            foreach ($page->sections as $section) {
                echo "Section $section->order : $section->name (pid:" . $section->page->id . " sid:$section->id)<br>-------------------------------------<br>";
                foreach ($section->questions as $question) {
                    echo "Q $question->id - $question->name (s:" . $question->section->id . ") &nbsp; T:$question->type  &nbsp; S:$question->type_special<br>";
                    if ($question->type == 'select' && count($question->options())) {
                        foreach ($question->options() as $opt) {
                            echo " &nbsp; &nbsp; [$opt->id] T:$opt->text V:$opt->value C:$opt->colour<br>";
                        }
                        echo "<br>";
                    }
                }
                echo "<br>";
            }
        }
    }


    /*
     * Create Template Form - SafetyInDesign
     */
    public function createFormTemplate1()
    {
        $now = Carbon::now()->format('d/m/Y g:i a');
        echo "<b>Creating Sample Form Template - $now</b></br>";


        // Creating Safety In Design Template
        $template = FormTemplate::create(['parent_id' => null, 'version' => '1.0', 'name' => 'Safety In Design Checklist', 'description' => 'The following criteria is to be established in order to prompt identification of potential hazards related to the existing conditions of a project and those arising from the associated proposed design and contract works. All identified hazards must be captured within the site-specific risk assessment. ', 'company_id' => 3, 'created_by' => 3, 'updated_by' => 3]);
        $tid = $template->id;
        $pn = 1;
        //
        // Page 1
        //
        $page = FormPage::create(['template_id' => $tid, 'name' => "Title Page", 'description' => null, 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 1a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Site", 'type' => "select", 'type_special' => 'site', 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3, 'placeholder' => 'Select site']);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Inspection date", 'type' => "datetime", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Inspected by", 'type' => "select", 'type_special' => 'staff', 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 0, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 0, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 0, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 0, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 2
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Existing Site", 'description' => null, 'order' => 2, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 2a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Existing structure", 'type' => "media", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Estimated age", 'type' => "text", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Type of construction", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "External Cladding", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Roof", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Orientation", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);


        //
        // Page 3
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Proposed Works", 'description' => '', 'order' => 3, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions -  Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 3a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Summary of proposed works and design scope", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Construction materials", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 4
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Proximity to Adjacent Properties and Infrastructure", 'description' => '', 'order' => 4, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions -  Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 4a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Property has or is adjacent to battle axe/right of way?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Are there any recreational facilities that exist nearby, such as parks, playgrounds, sporting fields?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Are there any public or government facilities nearby, such as schools, bus stops, recreational centres or community centres?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Are pedestrian paths/walkways in proximity to the property?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the site address positioned on or affected by a corner?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the site address positioned on or affected by a busy or main road?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);


        //
        // Page 5
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Project Position and Conditions", 'description' => '', 'order' => 5, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => 'Section 5a', 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the Client to reside in the home for the entirety of the construction, or portion of construction?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is a power supply outlet fitted to the electrical meter board (\"Builder's power\")", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Does the Client have any animals or pets residing on, or likely to be residing on the property at the time of construction?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'section', 'trigger_id' => 6, 'created_by' => 3, 'updated_by' => 3]);

        // Questions - Section 2
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => "Animals & Pets", 'description' => null, 'order' => 2, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Indicate the animals/pets on the property", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'placeholder' => 'Select one or more pet(s)', 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Dog(s)', 'value' => 'Dog(s)', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Cat(s)', 'value' => 'Cat(s)', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Pet Bird(s)', 'value' => 'Pet Bird(s)', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Livestock', 'value' => 'Livestock', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Horse(s)', 'value' => 'Horse(s)', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Other', 'value' => 'Other', 'order' => 6, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "What is the confinement or restraint practices available to control the risk of loss of pets or potential injury caused by interaction?", 'type' => "text", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        // Questions - Section 3
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 5c", 'order' => 3, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the Client aware of any adverse or aggravating factors in relationships with neighbours?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the client aware of any adverse conditions related to the property that may affect design or construction?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "What does the foundation of the structure comprise of?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Concrete slab', 'value' => 'Concrete slab', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Brick piers, bearers & joists', 'value' => 'Brick piers, bearers & joists', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Suspended slab', 'value' => 'Suspended slab', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the project, street or immediate vicinity of the project address subject to sloping?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 6
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Access", 'description' => '', 'order' => 6, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 6a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the project, or access to the project positioned on a narrow street?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is access to the property itself narrow or obstructed?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is use of adjacent streets or side access possible? (alleyways, side streets, back streets, rear access)", 'type' => "select", 'type_special' => 'YgN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is access via neighbouring properties possible (i.e. with neighbour permission)", 'type' => "select", 'type_special' => 'YgN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is access for deliveries foreseeably difficult?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is there adequate space for the storage of materials on site?", 'type' => "select", 'type_special' => 'YgN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '10', 'trigger' => 'question', 'trigger_id' => ($question->id + 1), 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Will a council permit be required for material storage?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 7
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Waste Management", 'description' => '', 'order' => 7, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 7a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Type of waste management system advised", 'type' => "select", 'type_special' => 'button', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Skip Bin', 'value' => 'Skip Bin', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Enclosure', 'value' => 'Enclosure', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '23', 'trigger' => 'section', 'trigger_id' => '10', 'created_by' => 3, 'updated_by' => 3]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '24', 'trigger' => 'section', 'trigger_id' => '11', 'created_by' => 3, 'updated_by' => 3]);

        // Questions - Section 2
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 7b", 'order' => 2, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Location", 'type' => "select", 'type_special' => 'button', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Driveway', 'value' => 'Driveway', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Front yard', 'value' => 'Front yard', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'On street', 'value' => 'On street', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Size", 'type' => "select", 'type_special' => null, 'type_version' => 'select2', 'placeholder' => 'Select size',
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => '3m (L)2.4m x (W)1.6m x (H)0.9m', 'value' => '3m (L)2.4m x (W)1.6m x (H)0.9m', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => '4m (L)3.2m x (W)1.6m x (H)0.9m', 'value' => '4m (L)3.2m x (W)1.6m x (H)0.9m', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => '6m (L)3.6m x (W)1.6m x (H)1.2m', 'value' => '6m (L)3.6m x (W)1.6m x (H)1.2m', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => '10m (L)4.3m x (W)1.6m x (H)1.5m', 'value' => '10m (L)4.3m x (W)1.6m x (H)1.5m', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => '14m (L)4.8m x (W)1.6m x (H)1.6m', 'value' => '14m (L)4.8m x (W)1.6m x (H)1.6m', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is a council permit required?", 'type' => "select", 'type_special' => 'YN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        // Questions - Section 3
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 7c", 'order' => 3, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Size", 'type' => "select", 'type_special' => 'button', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Large (metal)', 'value' => 'Large (metal)', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Small (timber)', 'value' => 'Small (timber)', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 8
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Underground or Overhead Services", 'description' => '', 'order' => 8, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 8a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "What type of electrical infrastructure supplies the property?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Overhead powerlines / point of attachment', 'value' => 'Overhead powerlines / point of attachment', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Private pole', 'value' => 'Private pole', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Underground supply to property', 'value' => 'Underground supply to property', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the property affected by nearby overhead powerlines?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'No', 'value' => 'No', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Parallel (left)', 'value' => 'Parallel (left)', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Parallel (right)', 'value' => 'Parallel (right)', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Front of property', 'value' => 'Front of property', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Rear of property', 'value' => 'Rear of property', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Intersecting property', 'value' => 'Intersecting property', 'order' => 6, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Has Dial Before You Dig been actioned as part of the design process?", 'type' => "select", 'type_special' => 'YN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '5', 'trigger' => 'section', 'trigger_id' => '13', 'created_by' => 3, 'updated_by' => 3]);

        // Questions - Section 2
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 8b", 'order' => 2, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => 'Detail', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => 'Are there any underground assets identified as affected (Detail of assets affected)', 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'question', 'trigger_id' => '48', 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => 'Detail of assets affected', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 9
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Environmental Conditions", 'description' => '', 'order' => 9, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 9a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the location of the project subject to any adverse environmental conditions?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Bushfire', 'value' => 'Bushfire', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Flooding', 'value' => 'Flooding', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Noise Pollution', 'value' => 'Noise Pollution', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Heritage', 'value' => 'Heritage', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Other', 'value' => 'Other', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Not applicable', 'value' => 'Not applicable', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=*', 'match_value' => '44,45,46,47,48', 'trigger' => 'question', 'trigger_id' => '50', 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => 'Detail', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 10
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Hazardous Materials", 'description' => '', 'order' => 10, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 10a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Has a hazardous materials survey been conducted?", 'type' => "select", 'type_special' => 'YgN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Has asbestos been identified on the property?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);


        //
        // Duplicate 'Publish' new template
        //
        $match_pages = [];
        $match_sections = [];
        $match_questions = [];
        $match_options = [];
        $t = FormTemplate::create(['parent_id' => $template->id, 'version' => $template->version, 'name' => $template->name, 'description' => $template->description, 'company_id' => $template->company_id, 'created_by' => 3, 'updated_by' => 3]);
        // Pages
        foreach ($template->pages as $page) {
            $p = FormPage::create(['template_id' => $t->id, 'name' => $page->name, 'description' => $page->description, 'order' => $page->order, 'created_by' => 3, 'updated_by' => 3]);
            $match_pages[$page->id] = $p->id;
            // Sections
            foreach ($page->sections as $section) {
                $s = FormSection::create(['template_id' => $t->id, 'page_id' => $p->id, 'parent' => $section->parent, 'name' => $section->name, 'description' => $section->description, 'order' => $section->order, 'created_by' => 3, 'updated_by' => 3]);
                $match_sections[$section->id] = $s->id;
                // Questions
                foreach ($section->questions as $question) {
                    $q = FormQuestion::create(['template_id' => $t->id, 'page_id' => $p->id, 'section_id' => $s->id, 'name' => $question->name, 'type' => $question->type, 'type_special' => $question->type_special, 'type_version' => $question->type_version,
                                               'order'       => $question->order, 'default' => $question->default, 'multiple' => $question->multiple, 'required' => $question->required, 'created_by' => 3, 'updated_by' => 3]);
                    $match_questions[$question->id] = $q->id;
                    // Question Options
                    foreach ($question->options() as $option) {
                        // Only create 'custom' options ie exclude master options ie (CONN, YN, YrY, YgN)
                        if (!$option->master) {
                            $o = FormOption::create(['question_id' => $q->id, 'text' => $option->text, 'value' => $option->value, 'order' => $option->order, 'score' => $option->score, 'colour' => $option->colour, 'group' => $option->group, 'master' => $option->master, 'status' => $option->status, 'created_by' => 3, 'updated_by' => 3]);
                            $match_options[$option->id] = $o->id;
                        } else
                            $match_options[$option->id] = $option->id;
                    }
                }
            }
        }
        //
        // Add logic (needs to be done after base templates copied to correct 'match' old question_ids to new Question Logic
        //
        foreach ($template->logic as $logic) {
            $new_match_value = '';
            $new_trigger_id = $logic->trigger_id;
            $question = FormQuestion::find($logic->question_id);

            // Convert the orig match_values if Original question is a select with custom values
            if ($question->type == 'select' && !in_array($question->type_special, ['site', 'staff', 'CONN', 'YN', 'YrN', 'YgN', 'YNNA'])) {
                $old_match_value = explode(',', $logic->match_value);
                foreach ($old_match_value as $val)
                    $new_match_value .= $match_options[$val] . ',';
                $new_match_value = rtrim($new_match_value, ',');
            } else
                $new_match_value = $logic->match_value;

            if ($logic->trigger == 'question') $new_trigger_id = $match_questions[$logic->trigger_id];
            if ($logic->trigger == 'section') $new_trigger_id = $match_sections[$logic->trigger_id];

            $l = FormLogic::create(['template_id' => $t->id, 'page_id' => $match_pages[$logic->page_id], 'question_id' => $match_questions[$logic->question_id], 'match_operation' => $logic->match_operation, 'match_value' => $new_match_value, 'trigger' => $logic->trigger, 'trigger_id' => $new_trigger_id, 'created_by' => 3, 'updated_by' => 3]);
        }
        $template->current_id = $t->id;
        $template->save();

        //
        // Create User Form
        //
        $form = Form::create(['template_id' => $t->id, 'name' => 'MyForm', 'company_id' => 3, 'created_by' => 3, 'updated_by' => 3]);

        /*echo "<br>Pages<br>";
        var_dump($match_pages);
        echo "<br>Sections<br>";
        var_dump($match_sections);
        echo "<br>Questions<br>";
        var_dump($match_questions);
        echo "<br>Options<br>";
        ksort($match_options);
        var_dump($match_options);*/
    }


    /*
    * Create Template Form - SafetyInDesign
    */
    public function createFormTemplate2()
    {
        $now = Carbon::now()->format('d/m/Y g:i a');
        echo "<b>Creating Sample Form Template - $now</b></br>";


        // Creating Safety In Design Template
        $template = FormTemplate::create(['parent_id' => null, 'version' => '1.0', 'name' => 'Safety In Design Checklist', 'description' => 'The following criteria is to be established in order to prompt identification of potential hazards related to the existing conditions of a project and those arising from the associated proposed design and contract works. All identified hazards must be captured within the site-specific risk assessment. ', 'company_id' => 3, 'created_by' => 3, 'updated_by' => 3]);
        $tid = $template->id;
        $pn = 1;
        //
        // Page 1
        //
        $page = FormPage::create(['template_id' => $tid, 'name' => "Title Page", 'description' => null, 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 1a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "Site", 'type' => "select", 'type_special' => 'site', 'type_version' => 'select2',
                'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3, 'placeholder' => 'Select site']);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "Inspection date", 'type' => "datetime", 'type_special' => null, 'type_version' => null,
                'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "Inspected by", 'type' => "select", 'type_special' => 'staff', 'type_version' => 'select2',
                'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
                'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 0, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
                'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 0, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
                'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 0, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
                'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 0, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 2
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Existing Site", 'description' => null, 'order' => 2, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 2a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "Existing structure", 'type' => "media", 'type_special' => null, 'type_version' => null,
                'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "Estimated age", 'type' => "text", 'type_special' => null, 'type_version' => null,
                'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "Type of construction", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
                'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "External Cladding", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
                'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "Roof", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
                'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name'        => "Orientation", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
                'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);


        //
        // Duplicate 'Publish' new template
        //
        $match_pages = [];
        $match_sections = [];
        $match_questions = [];
        $match_options = [];
        $t = FormTemplate::create(['parent_id' => $template->id, 'version' => $template->version, 'name' => $template->name, 'description' => $template->description, 'company_id' => $template->company_id, 'created_by' => 3, 'updated_by' => 3]);
        // Pages
        foreach ($template->pages as $page) {
            $p = FormPage::create(['template_id' => $t->id, 'name' => $page->name, 'description' => $page->description, 'order' => $page->order, 'created_by' => 3, 'updated_by' => 3]);
            $match_pages[$page->id] = $p->id;
            // Sections
            foreach ($page->sections as $section) {
                $s = FormSection::create(['template_id' => $t->id, 'page_id' => $p->id, 'parent' => $section->parent, 'name' => $section->name, 'description' => $section->description, 'order' => $section->order, 'created_by' => 3, 'updated_by' => 3]);
                $match_sections[$section->id] = $s->id;
                // Questions
                foreach ($section->questions as $question) {
                    $q = FormQuestion::create(['template_id' => $t->id, 'page_id' => $p->id, 'section_id' => $s->id, 'name' => $question->name, 'type' => $question->type, 'type_special' => $question->type_special, 'type_version' => $question->type_version,
                        'order'       => $question->order, 'default' => $question->default, 'multiple' => $question->multiple, 'required' => $question->required, 'created_by' => 3, 'updated_by' => 3]);
                    $match_questions[$question->id] = $q->id;
                    // Question Options
                    foreach ($question->options() as $option) {
                        // Only create 'custom' options ie exclude master options ie (CONN, YN, YrY, YgN)
                        if (!$option->master) {
                            $o = FormOption::create(['question_id' => $q->id, 'text' => $option->text, 'value' => $option->value, 'order' => $option->order, 'score' => $option->score, 'colour' => $option->colour, 'group' => $option->group, 'master' => $option->master, 'status' => $option->status, 'created_by' => 3, 'updated_by' => 3]);
                            $match_options[$option->id] = $o->id;
                        } else
                            $match_options[$option->id] = $option->id;
                    }
                }
            }
        }
        //
        // Add logic (needs to be done after base templates copied to correct 'match' old question_ids to new Question Logic
        //
        foreach ($template->logic as $logic) {
            $new_match_value = '';
            $new_trigger_id = $logic->trigger_id;
            $question = FormQuestion::find($logic->question_id);

            // Convert the orig match_values if Original question is a select with custom values
            if ($question->type == 'select' && !in_array($question->type_special, ['site', 'staff', 'CONN', 'YN', 'YrN', 'YgN', 'YNNA'])) {
                $old_match_value = explode(',', $logic->match_value);
                foreach ($old_match_value as $val)
                    $new_match_value .= $match_options[$val] . ',';
                $new_match_value = rtrim($new_match_value, ',');
            } else
                $new_match_value = $logic->match_value;

            if ($logic->trigger == 'question') $new_trigger_id = $match_questions[$logic->trigger_id];
            if ($logic->trigger == 'section') $new_trigger_id = $match_sections[$logic->trigger_id];

            $l = FormLogic::create(['template_id' => $t->id, 'page_id' => $match_pages[$logic->page_id], 'question_id' => $match_questions[$logic->question_id], 'match_operation' => $logic->match_operation, 'match_value' => $new_match_value, 'trigger' => $logic->trigger, 'trigger_id' => $new_trigger_id, 'created_by' => 3, 'updated_by' => 3]);
        }
        $template->current_id = $t->id;
        $template->save();

        //
        // Create User Form
        //
        $form = Form::create(['template_id' => $t->id, 'name' => 'MyForm', 'company_id' => 3, 'created_by' => 3, 'updated_by' => 3]);

        /*echo "<br>Pages<br>";
        var_dump($match_pages);
        echo "<br>Sections<br>";
        var_dump($match_sections);
        echo "<br>Questions<br>";
        var_dump($match_questions);
        echo "<br>Options<br>";
        ksort($match_options);
        var_dump($match_options);*/
    }
}

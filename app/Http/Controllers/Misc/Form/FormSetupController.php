<?php

namespace App\Http\Controllers\Misc\Form;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Misc\FilesystemIterator;
use App\Http\Controllers\Misc\RecursiveDirectoryIterator;
use App\Http\Controllers\Misc\RecursiveIteratorIterator;
use App\Http\Controllers\Misc\Response;
use App\Models\Comms\Todo;
use App\Models\Misc\Form\Form;
use App\Models\Misc\Form\FormLogic;
use App\Models\Misc\Form\FormOption;
use App\Models\Misc\Form\FormPage;
use App\Models\Misc\Form\FormQuestion;
use App\Models\Misc\Form\FormSection;
use App\Models\Misc\Form\FormTemplate;
use App\Models\Site\SiteQaAction;
use Carbon\Carbon;
use DB;
use Mail;
use Session;

class FormSetupController extends Controller
{

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

        Todo::where('type', 'inspection')->delete();

        //
        // Creating special options
        //
        echo "Creating Special Option</br>";
        // CONN
        FormOption::create(['text' => 'Compliant', 'value' => 'Compliant', 'order' => 1, 'colour' => 'green', 'score' => 2, 'group' => 'CONN', 'master' => 1, 'status' => 1]);
        FormOption::create(['text' => 'Improvement Opportunity', 'value' => 'Opportunity for improvement', 'order' => 2, 'colour' => 'yellow-saffron', 'score' => 1, 'group' => 'CONN', 'master' => 1, 'status' => 1]);
        FormOption::create(['text' => 'Non-compliant', 'value' => 'Non-compliant', 'order' => 3, 'colour' => 'red', 'score' => -2, 'group' => 'CONN', 'master' => 1, 'status' => 1]);
        FormOption::create(['text' => 'Not Applicable', 'value' => 'Not Applicable', 'order' => 4, 'colour' => null, 'score' => 0, 'group' => 'CONN', 'master' => 1, 'status' => 1]);
        // YN
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 1, 'colour' => null, 'group' => 'YN', 'master' => 1, 'status' => 1]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => 'YN', 'master' => 1, 'status' => 1]);
        // YrN
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 1, 'colour' => 'red', 'group' => 'YrN', 'master' => 1, 'status' => 1]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => 'YrN', 'master' => 1, 'status' => 1]);
        // YgN
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 1, 'colour' => 'green', 'group' => 'YgN', 'master' => 1, 'status' => 1]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => 'YgN', 'master' => 1, 'status' => 1]);
        // YNNA
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => 'YNNA', 'master' => 1, 'status' => 1]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => 'YNNA', 'master' => 1, 'status' => 1]);
        FormOption::create(['text' => 'N/A', 'value' => 'N/A', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => 'YNNA', 'master' => 1, 'status' => 1]);

        $this->createFormTemplate1();
        $this->createFormTemplate2();
    }

    //
    // Show FormTemplate
    //

    public function createFormTemplate1()
    {
        $now = Carbon::now()->format('d/m/Y g:i a');
        echo "<b>Creating Safety In Design Template - $now</b></br>";


        // Creating Safety In Design Template
        $template = FormTemplate::create(['parent_id' => null, 'version' => '1.0', 'name' => 'Safety In Design Checklist', 'description' => 'The following criteria is to be established in order to prompt identification of potential hazards related to the existing conditions of a project and those arising from the associated proposed design and contract works. All identified hazards must be captured within the site-specific risk assessment. ', 'company_id' => 3]);
        $tid = $template->id;
        $pn = 1;
        //
        // Page 1
        //
        $page = FormPage::create(['template_id' => $tid, 'name' => "Title Page", 'description' => null, 'order' => 1]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 1a", 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Site", 'type' => "select", 'type_special' => 'site', 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1, 'placeholder' => 'Select site']);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Inspection date", 'type' => "datetime", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Inspected by", 'type' => "select", 'type_special' => 'staff', 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 0]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 0]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 0]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 0]);

        //
        // Page 2
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Existing Site", 'description' => null, 'order' => 2]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 2a", 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Existing structure", 'type' => "media", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Estimated age", 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Type of construction", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "External Cladding", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Roof", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Orientation", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);


        //
        // Page 3
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Proposed Works", 'description' => '', 'order' => 3]);
        $pid = $page->id;

        // Questions -  Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 3a", 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Summary of proposed works and design scope", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Construction materials", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        //
        // Page 4
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Proximity to Adjacent Properties and Infrastructure", 'description' => '', 'order' => 4]);
        $pid = $page->id;

        // Questions -  Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 4a", 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Property has or is adjacent to battle axe/right of way?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Are there any recreational facilities that exist nearby, such as parks, playgrounds, sporting fields?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Are there any public or government facilities nearby, such as schools, bus stops, recreational centres or community centres?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Are pedestrian paths/walkways in proximity to the property?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is the site address positioned on or affected by a corner?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is the site address positioned on or affected by a busy or main road?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);


        //
        // Page 5
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Project Position and Conditions", 'description' => '', 'order' => 5]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => 'Section 5a', 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is the Client to reside in the home for the entirety of the construction, or portion of construction?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is a power supply outlet fitted to the electrical meter board (\"Builder's power\")", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Does the Client have any animals or pets residing on, or likely to be residing on the property at the time of construction?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'section', 'trigger_id' => 6]);

        // Questions - Section 2
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => "Animals & Pets", 'description' => null, 'order' => 2]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Indicate the animals/pets on the property", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1, 'placeholder' => 'Select one or more pet(s)']);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Dog(s)', 'value' => 'Dog(s)', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Cat(s)', 'value' => 'Cat(s)', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Pet Bird(s)', 'value' => 'Pet Bird(s)', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Livestock', 'value' => 'Livestock', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Horse(s)', 'value' => 'Horse(s)', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Other', 'value' => 'Other', 'order' => 6, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "What is the confinement or restraint practices available to control the risk of loss of pets or potential injury caused by interaction?", 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        // Questions - Section 3
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 5c", 'order' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is the Client aware of any adverse or aggravating factors in relationships with neighbours?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is the client aware of any adverse conditions related to the property that may affect design or construction?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "What does the foundation of the structure comprise of?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Concrete slab', 'value' => 'Concrete slab', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Brick piers, bearers & joists', 'value' => 'Brick piers, bearers & joists', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Suspended slab', 'value' => 'Suspended slab', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is the project, street or immediate vicinity of the project address subject to sloping?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        //
        // Page 6
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Access", 'description' => '', 'order' => 6]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 6a", 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is the project, or access to the project positioned on a narrow street?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is access to the property itself narrow or obstructed?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is use of adjacent streets or side access possible? (alleyways, side streets, back streets, rear access)", 'type' => "select", 'type_special' => 'YgN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is access via neighbouring properties possible (i.e. with neighbour permission)", 'type' => "select", 'type_special' => 'YgN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is access for deliveries foreseeably difficult?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is there adequate space for the storage of materials on site?", 'type' => "select", 'type_special' => 'YgN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '10', 'trigger' => 'question', 'trigger_id' => ($question->id + 1)]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Will a council permit be required for material storage?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        //
        // Page 7
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Waste Management", 'description' => '', 'order' => 7]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 7a", 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Type of waste management system advised", 'type' => "select", 'type_special' => 'button', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Skip Bin', 'value' => 'Skip Bin', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Enclosure', 'value' => 'Enclosure', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '23', 'trigger' => 'section', 'trigger_id' => '10']);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '24', 'trigger' => 'section', 'trigger_id' => '11']);

        // Questions - Section 2
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 7b", 'order' => 2]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Location", 'type' => "select", 'type_special' => 'button', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Driveway', 'value' => 'Driveway', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Front yard', 'value' => 'Front yard', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'On street', 'value' => 'On street', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Size", 'type' => "select", 'type_special' => null, 'type_version' => 'select2', 'placeholder' => 'Select size',
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => '3m (L)2.4m x (W)1.6m x (H)0.9m', 'value' => '3m (L)2.4m x (W)1.6m x (H)0.9m', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => '4m (L)3.2m x (W)1.6m x (H)0.9m', 'value' => '4m (L)3.2m x (W)1.6m x (H)0.9m', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => '6m (L)3.6m x (W)1.6m x (H)1.2m', 'value' => '6m (L)3.6m x (W)1.6m x (H)1.2m', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => '10m (L)4.3m x (W)1.6m x (H)1.5m', 'value' => '10m (L)4.3m x (W)1.6m x (H)1.5m', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => '14m (L)4.8m x (W)1.6m x (H)1.6m', 'value' => '14m (L)4.8m x (W)1.6m x (H)1.6m', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is a council permit required?", 'type' => "select", 'type_special' => 'YN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        // Questions - Section 3
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 7c", 'order' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Size", 'type' => "select", 'type_special' => 'button', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Large (metal)', 'value' => 'Large (metal)', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Small (timber)', 'value' => 'Small (timber)', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        //
        // Page 8
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Underground or Overhead Services", 'description' => '', 'order' => 8]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 8a", 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "What type of electrical infrastructure supplies the property?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Overhead powerlines / point of attachment', 'value' => 'Overhead powerlines / point of attachment', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Private pole', 'value' => 'Private pole', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Underground supply to property', 'value' => 'Underground supply to property', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is the property affected by nearby overhead powerlines?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'No', 'value' => 'No', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Parallel (left)', 'value' => 'Parallel (left)', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Parallel (right)', 'value' => 'Parallel (right)', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Front of property', 'value' => 'Front of property', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Rear of property', 'value' => 'Rear of property', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Intersecting property', 'value' => 'Intersecting property', 'order' => 6, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Has Dial Before You Dig been actioned as part of the design process?", 'type' => "select", 'type_special' => 'YN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '5', 'trigger' => 'section', 'trigger_id' => '13']);

        // Questions - Section 2
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 8b", 'order' => 2]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => 'Dial Before You Dig Details', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => 'Are there any underground assets identified as affected (Detail of assets affected)', 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'question', 'trigger_id' => '48']);

        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => 'Detail of assets affected', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        //
        // Page 9
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Environmental Conditions", 'description' => '', 'order' => 9]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 9a", 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is the location of the project subject to any adverse environmental conditions?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Bushfire', 'value' => 'Bushfire', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Flooding', 'value' => 'Flooding', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Noise Pollution', 'value' => 'Noise Pollution', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Heritage', 'value' => 'Heritage', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Other', 'value' => 'Other', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Not applicable', 'value' => 'Not applicable', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=*', 'match_value' => '44,45,46,47,48', 'trigger' => 'question', 'trigger_id' => '50']);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
                'name' => 'Environmental Details', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
                'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        //
        // Page 10
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Hazardous Materials", 'description' => '', 'order' => 10]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 10a", 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Has a hazardous materials survey been conducted?", 'type' => "select", 'type_special' => 'YgN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Has asbestos been identified on the property?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);


        //
        // Duplicate 'Publish' new template
        //
        $match_pages = [];
        $match_sections = [];
        $match_questions = [];
        $match_options = [];
        $t = FormTemplate::create(['parent_id' => $template->id, 'version' => $template->version, 'name' => $template->name, 'description' => $template->description, 'company_id' => $template->company_id]);
        // Pages
        foreach ($template->pages as $page) {
            $p = FormPage::create(['template_id' => $t->id, 'name' => $page->name, 'description' => $page->description, 'order' => $page->order]);
            $match_pages[$page->id] = $p->id;
            // Sections
            foreach ($page->sections as $section) {
                $s = FormSection::create(['template_id' => $t->id, 'page_id' => $p->id, 'parent' => $section->parent, 'name' => $section->name, 'description' => $section->description, 'order' => $section->order]);
                $match_sections[$section->id] = $s->id;
                // Questions
                foreach ($section->questions as $question) {
                    $q = FormQuestion::create(['template_id' => $t->id, 'page_id' => $p->id, 'section_id' => $s->id, 'name' => $question->name, 'type' => $question->type, 'type_special' => $question->type_special, 'type_version' => $question->type_version,
                        'order' => $question->order, 'default' => $question->default, 'multiple' => $question->multiple, 'required' => $question->required]);
                    $match_questions[$question->id] = $q->id;
                    // Question Options
                    foreach ($question->options() as $option) {
                        // Only create 'custom' options ie exclude master options ie (CONN, YN, YrY, YgN)
                        if (!$option->master) {
                            $o = FormOption::create(['question_id' => $q->id, 'text' => $option->text, 'value' => $option->value, 'order' => $option->order, 'score' => $option->score, 'colour' => $option->colour, 'group' => $option->group, 'master' => $option->master, 'status' => $option->status]);
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

            $l = FormLogic::create(['template_id' => $t->id, 'page_id' => $match_pages[$logic->page_id], 'question_id' => $match_questions[$logic->question_id], 'match_operation' => $logic->match_operation, 'match_value' => $new_match_value, 'trigger' => $logic->trigger, 'trigger_id' => $new_trigger_id]);
        }
        $template->current_id = $t->id;
        $template->save();

        //
        // Create User Form
        //
        $form = Form::create(['template_id' => $t->id, 'name' => 'MyForm', 'company_id' => 3]);

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
        echo "<b>Creating WHS Inspection Template - $now</b></br>";


        // Creating WHS Inspection Template
        $template = FormTemplate::create(['parent_id' => null, 'version' => '1.0', 'name' => 'Construction Site WHS Inspection', 'description' => '', 'company_id' => 3]);
        $tid = $template->id;
        $pn = 1;
        $sn = 1;
        //
        // Page 1
        //
        $page = FormPage::create(['template_id' => $tid, 'name' => "Title Page", 'description' => null, 'order' => 1]);
        $pid = $page->id;

        // Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 1a", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Site", 'type' => "select", 'type_special' => 'site', 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1, 'placeholder' => 'Select site']);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Inspection date", 'type' => "datetime", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Inspected by", 'type' => "select", 'type_special' => 'staff', 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        //
        // Page 2
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "General", 'description' => null, 'order' => 2]);
        $pid = $page->id;
        $sn = 1;

        // Section 1 - Site Info
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Site Information, Public Protection & Site Security', 'description' => "Section 2a", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Site adequately secured against entry by unauthorised persons?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Means of securing site", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1, 'placeholder' => 'Select one or more options']);
        $qid = $question->id;
        // Add Options
        $op1 = FormOption::create(['question_id' => $question->id, 'text' => 'Temporary Construction Fencing', 'value' => 'Temporary Construction Fencing', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op2 = FormOption::create(['question_id' => $question->id, 'text' => 'Existing fencing at premises', 'value' => 'Existing fencing at premises', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op3 = FormOption::create(['question_id' => $question->id, 'text' => 'Ply/boarding affixed to existing fencing/structure', 'value' => 'Ply/boarding affixed to existing fencing/structure', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op4 = FormOption::create(['question_id' => $question->id, 'text' => 'Livestock', 'value' => 'Livestock', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op5 = FormOption::create(['question_id' => $question->id, 'text' => 'Ramp Barricade', 'value' => 'Ramp Barricade', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op6 = FormOption::create(['question_id' => $question->id, 'text' => 'Other (provide detail)', 'value' => 'Other (provide detail)', 'order' => 6, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        // Logic (Other)
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => 'Securing Site Details', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op6->id, 'trigger' => 'question', 'trigger_id' => $question->id]);


        // Logic Section (Fence)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Fence', 'description' => "Section 1a", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1a - Fence
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is fencing at least 1.8m high?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'No vulnerable points identified? (Joints and panels should not present gaps and it should be difficult to gain access under the fence or to scale the fence)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Fences able to withstand anticipated loads to which they may be subjected? (such as wind, persons attempting to scale etc)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => "$op1->id,$op2->id,$op3->id", 'trigger' => 'section', 'trigger_id' => $sid2]);

        // Section 1b
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => '', 'description' => "Section 1b", 'order' => $sn++]);
        $sid2 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => "Public areas unobstructed/adequately protected?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => "Principal Contractor signage and emergency contact details displayed and clearly visible from outside the workplace?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => "Building Certifier details displayed?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => "Does the layout of the workplace effectively separate pedestrians, vehicles and powered mobile plant (as applicable)?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);


        // Section 2 - Access / Egress
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Access / Egress', 'description' => "Section 2", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Is the layout of the site maintained to allow persons to enter, exit, and move within it safely, both under normal working conditions and in an emergency?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Entry and exit areas and passageways kept free of obstruction from materials, waste and debris?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Means of entry and exit to work areas?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1, 'placeholder' => 'Select one or more options']);
        $qid = $question->id;
        // Add Options
        $op1 = FormOption::create(['question_id' => $question->id, 'text' => 'Ramp', 'value' => 'Ramp', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op2 = FormOption::create(['question_id' => $question->id, 'text' => 'Ladder(s)', 'value' => 'Ladder(s)', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op3 = FormOption::create(['question_id' => $question->id, 'text' => 'Scaffold', 'value' => 'Scaffold', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op4 = FormOption::create(['question_id' => $question->id, 'text' => 'Internal staircase', 'value' => 'Internal staircase', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op5 = FormOption::create(['question_id' => $question->id, 'text' => 'Level ground access (such as finished doors)', 'value' => 'Level ground access (such as finished doors)', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op6 = FormOption::create(['question_id' => $question->id, 'text' => 'Other (provide detail)', 'value' => 'Other (provide detail)', 'order' => 6, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        // Logic (Other)
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => 'Other Entry Details', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op6->id, 'trigger' => 'question', 'trigger_id' => $question->id]);

        // Logic Section (Ramp)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Ramps', 'description' => "Section 2a", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 2a - Ramp
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Ramp type', 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        // Add Options
        $op7 = FormOption::create(['question_id' => $question->id, 'text' => 'Aluminium Ramp', 'value' => 'Aluminium Ramp', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op8 = FormOption::create(['question_id' => $question->id, 'text' => 'Timber Ramp', 'value' => 'Timber Ramp', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);


        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Ramps in good condition, cleats maintained?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Suitable handrails in place?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Ramps appropriately secured and braced?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Ramps at suitable gradient? (Cleated surfaces should not be steeper than 20 degrees/1 in 3 gradient).', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op1->id, 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Logic Section (Ladders)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Ladders', 'description' => "Section 2b", 'order' => $sn++]);
        $sid2 = $section->id;

        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Ladders are maintained in good condition and rated for industrial use?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Ladders are set up on firm, stable ground and level ground?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Ladders are secured against displacement (i.e. slipping or sliding) and/or there is another person holding the base of the ladder?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Ladder set up at appropriate distance from the support structure? (distance between the ladder base and the supporting structure should be at a ratio of 4:1 - about one metre for every four metres of working ladder height)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Locking devices on ladders are secure?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Ladders extend at least one metre above the stepping-off point on the working platform (where used for access purposes)?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Ladder is the correct height for the task to avoid reaching or stretching?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Can three points of contact be maintained and tools can be operated safely with one hand (if working from the ladder)?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op2->id, 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Section 3 - Site Facilities
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Site Facilities', 'description' => "Section 3", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Provision of adequate facilities for workers, including toilets, drinking water, washing facilities and eating facilities?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Facilities provided", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1, 'placeholder' => 'Select one or more options']);
        $qid = $question->id;
        // Add Options
        $op1 = FormOption::create(['question_id' => $question->id, 'text' => 'Existing bathroom', 'value' => 'Existing bathroom', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op2 = FormOption::create(['question_id' => $question->id, 'text' => 'Self-contained portable toilet', 'value' => 'Self-contained portable toilet', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op3 = FormOption::create(['question_id' => $question->id, 'text' => 'Potable drinking water', 'value' => 'Potable drinking water', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op4 = FormOption::create(['question_id' => $question->id, 'text' => 'Handwashing facilities', 'value' => 'Handwashing facilities', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op5 = FormOption::create(['question_id' => $question->id, 'text' => 'Other (provide detail)', 'value' => 'Other (provide detail)', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        // Logic (Other)
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => 'Other Facilities Details', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op5->id, 'trigger' => 'question', 'trigger_id' => $question->id]);

        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Facilities are maintained in good working order and are clean, safe and accessible? (Self-contained fresh water flushing portable toilets must be regularly serviced in accordance with the supplier’s information and instructions, but not less than monthly).", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Provision of first aid equipment and worker access to the equipment (such as a stocked first aid kit)?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        // Section 4 - General
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'General', 'description' => "Section 3", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Adequate designated waste storage/placement points?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Good general housekeeping practices maintained?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Adequate dust screening in place?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Roof tarps secured?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Deck polyfabric secured?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Adequate natural ventilation / provision of artificial ventilation (such as exhaust fans) as required?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Appropriate storage of materials?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);


        //
        // Page 3
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Workers and Tasks", 'description' => null, 'order' => 3]);
        $pid = $page->id;
        $sn = 1;

        // Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Site Information, Public Protection & Site Security', 'description' => "Section 2a", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Have all workers completed site sign in?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Others on site", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1, 'placeholder' => 'Select one or more options']);
        $qid = $question->id;
        // Add Options
        $op1 = FormOption::create(['question_id' => $question->id, 'text' => 'N/A - Nil others on site', 'value' => 'N/A - Nil others on site', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op2 = FormOption::create(['question_id' => $question->id, 'text' => 'Client', 'value' => 'Client', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op3 = FormOption::create(['question_id' => $question->id, 'text' => 'Client appointed trades (provide detail)', 'value' => 'Client appointed trades (provide detail)', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op4 = FormOption::create(['question_id' => $question->id, 'text' => 'Cape Cod staff (provide detail)', 'value' => 'Cape Cod staff (provide detail)', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op5 = FormOption::create(['question_id' => $question->id, 'text' => 'Engineer', 'value' => 'Engineer', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op6 = FormOption::create(['question_id' => $question->id, 'text' => 'Supervisor', 'value' => 'Supervisor', 'order' => 6, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op7 = FormOption::create(['question_id' => $question->id, 'text' => 'Surveyor', 'value' => 'Surveyor', 'order' => 7, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        // Logic (Other)
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => 'Others on Site Details', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => "$op3->id,$op4->id", 'trigger' => 'question', 'trigger_id' => $question->id]);

        // Questions cont...
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Are workers aware of the process and requirements for reporting hazards?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Are workers aware of the process and requirements for reporting incidents (such as injuries, damage)?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Are workers aware of how to obtain the asbestos register/hazardous materials information for the site?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Do any of the following aspects or risks apply to work being performed?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1, 'placeholder' => 'Select one or more options']);
        $qid = $question->id;
        // Add Options
        $op1 = FormOption::create(['question_id' => $question->id, 'text' => 'Not Applicable - Nil workers on site', 'value' => 'Not Applicable - Nil workers on site', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op2 = FormOption::create(['question_id' => $question->id, 'text' => 'Nil Applicable to task', 'value' => 'Nil Applicable to task', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op3 = FormOption::create(['question_id' => $question->id, 'text' => 'Asbestos', 'value' => 'Asbestos', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op4 = FormOption::create(['question_id' => $question->id, 'text' => 'Chemicals / Hazardous Substances', 'value' => ' Chemicals / Hazardous Substances', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op5 = FormOption::create(['question_id' => $question->id, 'text' => 'Chemical, fuel or refrigerant lines - work on or near', 'value' => 'Chemical, fuel or refrigerant lines - work on or near', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op6 = FormOption::create(['question_id' => $question->id, 'text' => 'Confined Spaces - Work in or near a (legislated term of) confined space', 'value' => 'Confined Spaces - Work in or near a (legislated term of) confined space', 'order' => 6, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op7 = FormOption::create(['question_id' => $question->id, 'text' => 'Demolition of load-bearing structure', 'value' => 'Demolition of load-bearing structure', 'order' => 7, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op8 = FormOption::create(['question_id' => $question->id, 'text' => 'Other Dust / Airborne contaminants', 'value' => 'Other Dust / Airborne contaminants', 'order' => 8, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op9 = FormOption::create(['question_id' => $question->id, 'text' => 'Electrical', 'value' => 'Electrical', 'order' => 9, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op10 = FormOption::create(['question_id' => $question->id, 'text' => 'Excavation / Ground Penetration', 'value' => 'Excavation / Ground Penetration', 'order' => 10, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op11 = FormOption::create(['question_id' => $question->id, 'text' => 'Extremes of temperature', 'value' => 'Extremes of temperature', 'order' => 11, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op12 = FormOption::create(['question_id' => $question->id, 'text' => 'Gas mains or piping - Work on or near', 'value' => 'Gas mains or piping - Work on or near', 'order' => 12, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op13 = FormOption::create(['question_id' => $question->id, 'text' => 'Hot Works', 'value' => 'Hot Works', 'order' => 13, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op14 = FormOption::create(['question_id' => $question->id, 'text' => 'Manual Handling', 'value' => 'Manual Handling', 'order' => 14, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op15 = FormOption::create(['question_id' => $question->id, 'text' => 'Noise', 'value' => 'Noise', 'order' => 15, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op15 = FormOption::create(['question_id' => $question->id, 'text' => 'Powered Mobile Plant', 'value' => 'Powered Mobile Plant', 'order' => 15, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op16 = FormOption::create(['question_id' => $question->id, 'text' => 'Temporary load bearing support for structural alterations or repairs', 'value' => 'Temporary load bearing support for structural alterations or repairs', 'order' => 16, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op17 = FormOption::create(['question_id' => $question->id, 'text' => 'Tilt-up or pre-cast concrete elements', 'value' => 'Tilt-up or pre-cast concrete elements', 'order' => 17, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op18 = FormOption::create(['question_id' => $question->id, 'text' => 'Water - Work in or near water or other liquid that involves a risk of drowning', 'value' => 'Water - Work in or near water or other liquid that involves a risk of drowning', 'order' => 18, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op19 = FormOption::create(['question_id' => $question->id, 'text' => 'Working at heights', 'value' => 'Working at heights', 'order' => 19, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op20 = FormOption::create(['question_id' => $question->id, 'text' => 'Young / Vulnerable workers', 'value' => 'Young / Vulnerable workers', 'order' => 20, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        // Logic Section (Asbestos)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Asbestos', 'description' => "Section 1a", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1a - Asbestos
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Are workers aware as to the location of any identified asbestos?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Asbestos/hazardous materials risks controlled?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Has a SWMS been completed, submitted to Cape Cod and being worked in accordance to where the work is likely to involve the disturbance of asbestos?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is asbestos removal taking place?', 'type' => "select", 'type_special' => 'YN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid3 = $question->id;
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op3->id, 'trigger' => 'section', 'trigger_id' => $sid2]);

        // Sub Section 1b - Asbestos Removal
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid2, 'name' => 'Asbestos Removal', 'description' => "Section 1b", 'order' => $sn++]);
        $sid3 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid3,
            'name' => 'Are all asbestos removal workers/workers involved in the removal task and area competent? (formal competency attained)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid3,
            'name' => 'Has an asbestos notification been submitted via the SafeWorkSite?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid3,
            'name' => 'Is the asbestos removal area appropriately isolated?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid3, 'match_operation' => '=', 'match_value' => '5', 'trigger' => 'section', 'trigger_id' => $sid3]);

        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid3, 'name' => 'Asbestos Removal Child', 'description' => "Section 1b", 'order' => $sn++]);


        //
        // Duplicate 'Publish' new template
        //
        $match_pages = [];
        $match_sections = [];
        $match_questions = [];
        $match_options = [];
        $t = FormTemplate::create(['parent_id' => $template->id, 'version' => $template->version, 'name' => $template->name, 'description' => $template->description, 'company_id' => $template->company_id]);
        // Pages
        foreach ($template->pages as $page) {
            $p = FormPage::create(['template_id' => $t->id, 'name' => $page->name, 'description' => $page->description, 'order' => $page->order]);
            $match_pages[$page->id] = $p->id;
            // Sections
            foreach ($page->sections as $section) {
                $parent_section = ($section->parent) ? $match_sections[$section->parent] : null;
                $s = FormSection::create(['template_id' => $t->id, 'page_id' => $p->id, 'parent' => $parent_section, 'name' => $section->name, 'description' => $section->description, 'order' => $section->order]);
                $match_sections[$section->id] = $s->id;
                // Questions
                foreach ($section->questions as $question) {
                    $q = FormQuestion::create(['template_id' => $t->id, 'page_id' => $p->id, 'section_id' => $s->id, 'name' => $question->name, 'type' => $question->type, 'type_special' => $question->type_special, 'type_version' => $question->type_version,
                        'order' => $question->order, 'default' => $question->default, 'multiple' => $question->multiple, 'required' => $question->required]);
                    $match_questions[$question->id] = $q->id;
                    // Question Options
                    foreach ($question->options() as $option) {
                        // Only create 'custom' options ie exclude master options ie (CONN, YN, YrY, YgN)
                        if (!$option->master) {
                            $o = FormOption::create(['question_id' => $q->id, 'text' => $option->text, 'value' => $option->value, 'order' => $option->order, 'score' => $option->score, 'colour' => $option->colour, 'group' => $option->group, 'master' => $option->master, 'status' => $option->status]);
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

            $l = FormLogic::create(['template_id' => $t->id, 'page_id' => $match_pages[$logic->page_id], 'question_id' => $match_questions[$logic->question_id], 'match_operation' => $logic->match_operation, 'match_value' => $new_match_value, 'trigger' => $logic->trigger, 'trigger_id' => $new_trigger_id]);
        }
        //$template->current_id = $t->id;
        //$template->save();

        //
        // Create User Form
        //
        $form = Form::create(['template_id' => $t->id, 'name' => 'MyForm', 'company_id' => 3]);

        /*echo "<br>Pages<br>";
        var_dump($match_pages);
        echo "<br>Sections<br>";
        var_dump($match_sections);
        echo "<br>Questions<br>";
        var_dump($match_questions);
        echo "<br>Options<br>";
        ksort($match_options);
        var_dump($match_options);*/


        $test = [];

        // Section 1
        $test[] = ['section' => 'Section 1', 'child' => []];

        // Section 2
        $c2a = ['Section 2a', 'child' => []];
        $test[] = ['section' => 'Section 2', 'child' => $c2a];

        // Section 3
        $c3aa = ['Section 3aa', 'child' => []];
        $c3a = ['Section 3a', 'child' => $c3aa];
        $test[] = ['section' => 'Section 3', 'child' => $c3a];

        dd($test);
    }


    /*
    * Create Template Form - WHS Inspection
    */

    public function showTemplate($id)
    {
        $template = FormTemplate::find($id);

        echo "Name: $template->name ($template->description)<br>";
        echo "P:" . $template->pages->count() . " S:" . $template->sections->count() . " Q:" . $template->questions->count() . "<br>-----------<br><br>";

        foreach ($template->pages as $page) {
            echo "<br>=====================================<br>Page $page->id : $page->name<br>=====================================<br>";
            foreach ($page->sections as $section) {
                echo "Section $section->order : $section->name (pid:" . $section->page->id . " sid:$section->id  psid:$section->parent)<br>-------------------------------------<br>";
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
}

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
        DB::table('forms_notes')->truncate();
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
        // YgNr
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 0, 'colour' => 'green', 'group' => 'YrNg', 'master' => 1, 'status' => 1]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 1, 'colour' => 'red', 'group' => 'YrNg', 'master' => 1, 'status' => 1]);

        $this->createFormTemplate1();
        $this->createFormTemplate2();
    }

    //
    // Show FormTemplate
    //

    public function createFormTemplate0()
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
                        // Only create 'custom' options ie exclude master options ie (CONN, YN, YrY, YgN, YgNr)
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
            if ($question->type == 'select' && !in_array($question->type_special, ['site', 'staff', 'CONN', 'YN', 'YrN', 'YgN', 'YgNr', 'YNNA'])) {
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
     * Create Template Form - Project Aspects & Conditions
     */
    public function createFormTemplate1()
    {
        $now = Carbon::now()->format('d/m/Y g:i a');
        echo "<b>Creating Project Aspects & Conditions Template - $now</b></br>";


        // Creating Safety In Design Template
        $template = FormTemplate::create(['parent_id' => null, 'version' => '1.0', 'name' => 'Project Aspects & Conditions', 'description' => 'The following criteria is to be established in order to prompt identification of potential hazards related to the existing conditions of a project and those arising from the associated proposed design and contract works. All identified hazards must be captured within the site-specific risk assessment.', 'company_id' => 3]);
        $tid = $template->id;
        $pn = 1;

        //
        // Page 1
        //
        $page = FormPage::create(['template_id' => $tid, 'name' => "Title Page", 'description' => null, 'order' => 1]);
        $pid = $page->id;
        $sn = 1;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 1a", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Site", 'type' => "select", 'type_special' => 'site', 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1, 'placeholder' => 'Select site']);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Date initiated", 'type' => "datetime", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Lot Size", 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 0]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Local Government Area / Council", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 0, 'placeholder' => 'Select option']);
        $qid = $question->id;
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Auburn City Council', 'value' => 'Auburn City Council', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Blue Mountains City Council', 'value' => 'Blue Mountains City Council', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Camden Council', 'value' => 'Camden Council', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Blacktown', 'value' => 'City of Blacktown', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Campbelltown', 'value' => 'City of Campbelltown', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Canada Bay Council', 'value' => 'City of Canada Bay Council', 'order' => 6, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Fairfield', 'value' => 'City of Fairfield', 'order' => 7, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Liverpool', 'value' => 'City of Liverpool', 'order' => 8, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Parramatta Council', 'value' => 'City of Parramatta Council', 'order' => 9, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Penrith', 'value' => 'City of Penrith', 'order' => 10, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Randwick', 'value' => 'City of Randwick', 'order' => 11, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Rockdale', 'value' => 'City of Rockdale', 'order' => 12, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Ryde', 'value' => 'City of Ryde', 'order' => 13, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Shoalhaven', 'value' => 'City of Shoalhaven', 'order' => 14, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Sydney', 'value' => 'City of Sydney', 'order' => 15, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'City of Willoughby', 'value' => 'City of Willoughby', 'order' => 16, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Cumberland City Council', 'value' => 'Cumberland City Council', 'order' => 17, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Hawkesbury City Council', 'value' => 'Hawkesbury City Council', 'order' => 18, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Hornsby Shire', 'value' => 'Hornsby Shire', 'order' => 19, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Ku-ring-gai Council', 'value' => 'Ku-ring-gai Council', 'order' => 20, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Mosman Municipal Council', 'value' => 'Mosman Municipal Council', 'order' => 21, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Municipality of Burwood', 'value' => 'Municipality of Burwood', 'order' => 22, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Municipality of Hunters Hill', 'value' => 'Municipality of Hunters Hill', 'order' => 23, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Municipality of Lane Cove', 'value' => 'Municipality of Lane Cove', 'order' => 24, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Municipality of Strathfield', 'value' => 'Municipality of Strathfield', 'order' => 25, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'North Sydney Council', 'value' => 'North Sydney Council', 'order' => 26, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Shellharbour', 'value' => 'Shellharbour', 'order' => 27, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Sutherland Shire Council', 'value' => 'Sutherland Shire Council', 'order' => 28, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'The Council of the City of Botany Bay', 'value' => 'The Council of the City of Botany Bay', 'order' => 29, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'The Hills Shire', 'value' => 'The Hills Shire', 'order' => 30, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Waverley Council', 'value' => 'Waverley Council', 'order' => 31, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Wollondilly Shire Council', 'value' => 'Wollondilly Shire Council', 'order' => 32, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Woollahra Municipality', 'value' => 'Woollahra Municipality', 'order' => 33, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op = FormOption::create(['question_id' => $question->id, 'text' => 'Other', 'value' => 'Other (specify)', 'order' => 34, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

        // Logic (Other)
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => 'Other LGA', 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op->id, 'trigger' => 'question', 'trigger_id' => $question->id]);


        //
        // Page 2 - Existing Site
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Existing Site", 'description' => null, 'order' => 2]);
        $pid = $page->id;
        $sn = 1;

        // Section 1a - Structure
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Structure', 'description' => "Section 1a", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Orientation", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Type of construction", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "External Cladding", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Existing structure", 'type' => "media", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Estimated age", 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        // Section 1b - Roof
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Roof', 'description' => "Section 1b", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Existing Roof material and construction", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Sarking to existing roof", 'type' => "select", 'type_special' => 'YgNr', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Damaged or deteriorated condition of existing roof", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;
        // Logic (Other)
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => 'Roof Condition Details', 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => '7', 'trigger' => 'question', 'trigger_id' => $question->id]);


        // Section 1c - Foundation
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Foundation', 'description' => "Section 1c", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Evidence of settling", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);

        // Section 1d - Zoning
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Zoning', 'description' => "Section 1d", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Project Zoning", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;
        $on = 1;
        $op1 = FormOption::create(['question_id' => $question->id, 'text' => 'R1', 'value' => 'R1', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op2 = FormOption::create(['question_id' => $question->id, 'text' => 'R2', 'value' => 'R2', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op3 = FormOption::create(['question_id' => $question->id, 'text' => 'R3', 'value' => 'R3', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op4 = FormOption::create(['question_id' => $question->id, 'text' => 'R4', 'value' => 'R4', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op5 = FormOption::create(['question_id' => $question->id, 'text' => 'RU5', 'value' => 'RU5', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        // Logic (Other)
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => 'If Zoning is NOT the standard R2 Low Residential Zoning, Ensure Controls collected are for the correct zone', 'type' => null, 'type_special' => 'no-resp', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 0]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=', 'match_value' => $op5->id, 'trigger' => 'question', 'trigger_id' => $question->id]);

        // Section 1e - Preliminary Compliance Checks
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Preliminary Compliance Checks', 'description' => 'Section 1e', 'order' => $sn++]);
        $sid2 = $section->id;

        // Sub Section 1e1 - Flood Zone
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid2, 'name' => '', 'description' => 'Section 1e1', 'order' => $sn++]);
        $sid3 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid3,
            'name' => "Flood Zone / Flood Planning considerations", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;
        // Logic (Y)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid3, 'name' => '', 'description' => "Section 1e1a", 'order' => $sn++]);
        $sid4 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Date Report requested", 'type' => "datetime", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Report", 'type' => "media", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'section', 'trigger_id' => $sid4]);

        // Sub Section 1e2 - Acid Sulphate
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid2, 'name' => '', 'description' => 'Section 1e2', 'order' => $sn++]);
        $sid3 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid3,
            'name' => "Acid Sulphate Soils risk", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;
        // Logic (Y)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid3, 'name' => '', 'description' => "Section 1e2a", 'order' => $sn++]);
        $sid4 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Date Report requested", 'type' => "datetime", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Report", 'type' => "media", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'section', 'trigger_id' => $sid4]);

        // Sub Section 1e3 - Bushfire Zone
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid2, 'name' => '', 'description' => 'Section 1e3', 'order' => $sn++]);
        $sid3 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid3,
            'name' => "Bushfire Zone", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;
        // Logic (Y)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid3, 'name' => '', 'description' => "Section 1e3a", 'order' => $sn++]);
        $sid4 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Closest fire hydrant >60m from farthest point", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Fire Hydrant distance from property", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'section', 'trigger_id' => $sid4]);

        // Sub Section 1e4 - Heritage listed or impacted
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid2, 'name' => '', 'description' => 'Section 1e4', 'order' => $sn++]);
        $sid3 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid3,
            'name' => "Heritage listed or impacted", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;
        // Logic (Y)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid3, 'name' => '', 'description' => "Section 1e4a", 'order' => $sn++]);
        $sid4 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Date Report requested", 'type' => "datetime", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Report", 'type' => "media", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'section', 'trigger_id' => $sid4]);

        // Sub Section 1e5 - Acoustic Controls
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid2, 'name' => '', 'description' => 'Section 1e5', 'order' => $sn++]);
        $sid3 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid3,
            'name' => "Acoustic Controls", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;
        // Logic (Y)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid3, 'name' => '', 'description' => "Section 1e5a", 'order' => $sn++]);
        $sid4 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Date Report requested", 'type' => "datetime", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Report", 'type' => "media", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'section', 'trigger_id' => $sid4]);

        // Sub Section 1e6 - Activity Hazard
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid2, 'name' => '', 'description' => 'Section 1e6', 'order' => $sn++]);
        $sid3 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid3,
            'name' => "Activity Hazard", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;
        // Logic (Y)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid3, 'name' => '', 'description' => "Section 1e6a", 'order' => $sn++]);
        $sid4 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Date Report requested", 'type' => "datetime", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Report", 'type' => "media", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'section', 'trigger_id' => $sid4]);

        // Sub Section 1e7 - Dual Occupancy Prohibition / Restriction
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid2, 'name' => '', 'description' => 'Section 1e7', 'order' => $sn++]);
        $sid3 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid3,
            'name' => "Dual Occupancy Prohibition / Restriction", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;
        // Logic (Y)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid3, 'name' => '', 'description' => "Section 1e7a", 'order' => $sn++]);
        $sid4 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Restriction details", 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'section', 'trigger_id' => $sid4]);

        // Sub Section 1e8 - Landslide Risk
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid2, 'name' => '', 'description' => 'Section 1e8', 'order' => $sn++]);
        $sid3 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid3,
            'name' => "Landslide Risk", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;
        // Logic (Y)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid3, 'name' => '', 'description' => "Section 1e8a", 'order' => $sn++]);
        $sid4 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Date Report requested", 'type' => "datetime", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Report", 'type' => "media", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'section', 'trigger_id' => $sid4]);

        // Sub Section 1e9 - Property subject to covenants
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid2, 'name' => '', 'description' => 'Section 1e9', 'order' => $sn++]);
        $sid3 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid3,
            'name' => "Property subject to covenants", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;
        // Logic (Y)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid3, 'name' => '', 'description' => "Section 1e9a", 'order' => $sn++]);
        $sid4 = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Covenants details", 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'section', 'trigger_id' => $sid4]);


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
            'name' => "Proposed works by Owner", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);


        //
        // Page 4
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Project Position and Conditions", 'description' => '', 'order' => 4]);
        $pid = $page->id;

        // Section 1 - Proximity to Adjacent Properties and Infrastructure
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Proximity to Adjacent Properties and Infrastructure', 'description' => "Section 4a", 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid4,
            'name' => "Speed Limit in immediate vicinity", 'type' => "text", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Number of lanes at immediate access road(s)", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Pedestrian footpath intersecting site access", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Clearway within 100m of site", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Bus or transit lane located within 100m of site", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Bus stop within 200m of site", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Roundabout within 100m of the site", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Has or is adjacent to battle axe/right of way", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Within 100m of intersection with traffic lights", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Within 100m of intersection without traffic lights", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Site positioned on or affected by a corner", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Site positioned on/affected by a busy or main road", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);


        // Section 2 - Recreational Facilities
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Recreational Facilities', 'description' => "Section 4b", 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Public or government facilities nearby", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1, 'placeholder' => 'Select one or more facilities']);
        $qid = $question->id;
        // Add Options
        $on = 1;
        $op1 = FormOption::create(['question_id' => $question->id, 'text' => 'School', 'value' => 'School', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op2 = FormOption::create(['question_id' => $question->id, 'text' => 'Childcare Facility', 'value' => 'Childcare Facility', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op3 = FormOption::create(['question_id' => $question->id, 'text' => 'Recreational Facility', 'value' => 'Recreational Facility', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op4 = FormOption::create(['question_id' => $question->id, 'text' => 'Park', 'value' => 'Park', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op5 = FormOption::create(['question_id' => $question->id, 'text' => 'Sporting Ground', 'value' => 'Sporting Ground', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op6 = FormOption::create(['question_id' => $question->id, 'text' => 'Shopping Centre', 'value' => 'Shopping Centre', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op7 = FormOption::create(['question_id' => $question->id, 'text' => 'Service Station', 'value' => 'Service Station', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op8 = FormOption::create(['question_id' => $question->id, 'text' => 'Other', 'value' => 'Other', 'order' => $on++, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        // Logic (Other)
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => 'Detail of facilities', 'type' => 'textarea', 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 0]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => "$op1->id,$op2->id,$op3->id,$op4->id,$op5->id,$op6->id,$op7->id,$op8->id", 'trigger' => 'question', 'trigger_id' => $question->id]);


        // Section 3 - Client/Stakeholders
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Client/Stakeholders', 'description' => "Section 4c", 'order' => 1]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Animals/Pets to reside on site during construction", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;
        /*

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
        */

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
                        'order' => $question->order, 'default' => $question->default, 'multiple' => $question->multiple, 'required' => $question->required, 'helper' => $question->helper]);
                    $match_questions[$question->id] = $q->id;
                    // Question Options
                    foreach ($question->options() as $option) {
                        // Only create 'custom' options ie exclude master options ie (CONN, YN, YrY, YgN, YgNr)
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
            if ($question->type == 'select' && !in_array($question->type_special, ['site', 'staff', 'CONN', 'YN', 'YrN', 'YgN', 'YgNr', 'YNNA'])) {
                $old_match_value = explode(',', str_replace(' ', '', $logic->match_value));
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
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Site Information, Public Protection & Site Security', 'description' => "Section 1", 'order' => $sn++]);
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
        $op16 = FormOption::create(['question_id' => $question->id, 'text' => 'Powered Mobile Plant', 'value' => 'Powered Mobile Plant', 'order' => 16, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op17 = FormOption::create(['question_id' => $question->id, 'text' => 'Temporary load bearing support for structural alterations or repairs', 'value' => 'Temporary load bearing support for structural alterations or repairs', 'order' => 17, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op18 = FormOption::create(['question_id' => $question->id, 'text' => 'Tilt-up or pre-cast concrete elements', 'value' => 'Tilt-up or pre-cast concrete elements', 'order' => 18, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op19 = FormOption::create(['question_id' => $question->id, 'text' => 'Water - Work in or near water or other liquid that involves a risk of drowning', 'value' => 'Water - Work in or near water or other liquid that involves a risk of drowning', 'order' => 19, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op20 = FormOption::create(['question_id' => $question->id, 'text' => 'Working at heights', 'value' => 'Working at heights', 'order' => 20, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $op21 = FormOption::create(['question_id' => $question->id, 'text' => 'Young / Vulnerable workers', 'value' => 'Young / Vulnerable workers', 'order' => 21, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);

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

        // Sub Section 1aa - Asbestos Removal
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid2, 'name' => 'Asbestos Removal', 'description' => "Section 1aa", 'order' => $sn++]);
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

        // Logic Section (Chemicals / Hazard Substances)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Chemicals / Hazardous Substances', 'description' => "Section 1b", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1b
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is a Safety Data Sheet readily available for reference for the chemical/substance being used?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is the appropriate PPE provided and being used correctly in reference to the chemical/substance?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is there adequate ventilation as required?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op4->id, 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Logic Section (Safe Work Method Statements for High Risk Construction Work)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Safe Work Method Statements for High Risk Construction Work', 'description' => "Section 1c", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1c
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Has a Safe Work Method Statement been completed and submitted to Cape Cod?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is the work being conducted in accordance with the SWMS?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Are workers aware of the location of the SWMS?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => "$op5->id,$op6->id,$op7->id,$op9->id,$op12->id,$op17->id,$op18->id,$op19->id", 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Logic Section (Dust / Airborne contaminants)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Dust / Airborne contaminants', 'description' => "Section 1d", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1d
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is dust being adequately controlled and/or managed? (e.g. ventilation, extraction fans, dust catcher/vacuum etc)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is the appropriate PPE being worn?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op8->id, 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Logic Section (Electrical)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Electrical', 'description' => "Section 1e", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1e
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Approach distances for work near low voltage overhead service lines and/or overhead powerlines adhered to?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1, 'helper' => '<img src="/img/form/electrical_approach_distance_work_performed.jpeg" class="img-responsive"> <img src="/img/form/electrical_approach_distance_work_near_low_volatage.jpeg" class="img-responsive">']);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Tiger tails installed as applicable to provide visual indicator as to presence of overhead powerlines nearby work activities/areas', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Electricity appropriately terminated/isolated in reference to work taking place, including demolition?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op9->id, 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Logic Section (Excavation)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Excavation', 'description' => "Section 1f", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1f
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Has a SWMS been prepared and provided to Cape Cod where excavation at is a depth greater than 1.5m?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Has current underground essential services information (Dial Before You Dig) been obtained for areas where the excavation work is being carried out and and readily available for inspection to any worker, Cape Cod and subcontractors?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op10->id, 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Logic Section (Extremes of temperature)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Extremes of temperature', 'description' => "Section 1g", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1g
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is appropriate PPE and sun protection as applicable supplied and being used correctly?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is there evidence of appropriate controls in place to address extremes of temperature? (such as adequate hydration, rest areas and breaks, task rotation)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Are workers aware of signs and symptoms of dehydration/heat illness? (assess through conversation)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op11->id, 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Logic Section (Hot Works)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Hot Works', 'description' => "Section 1h", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1h
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'What is the nature of the hot work? ', 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => 1, 'required' => 1]);
        $qid4 = $question->id;
        $opp1 = FormOption::create(['question_id' => $question->id, 'text' => 'Grinding', 'value' => 'Grinding', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $opp2 = FormOption::create(['question_id' => $question->id, 'text' => 'Welding', 'value' => 'Welding', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $opp3 = FormOption::create(['question_id' => $question->id, 'text' => 'Silver solder brazing', 'value' => 'Silver solder brazing', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $opp4 = FormOption::create(['question_id' => $question->id, 'text' => 'Oxy cutting', 'value' => 'Oxy cutting', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        $opp5 = FormOption::create(['question_id' => $question->id, 'text' => 'Other (provide detail)', 'value' => 'Other (provide detail)', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1]);
        // Logic (Other)
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Others Hot Work Details', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid4, 'match_operation' => '=*', 'match_value' => "$opp5->id", 'trigger' => 'question', 'trigger_id' => $question->id]);

        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is the appropriate fire control equipment available & in appropriate working order (i.e. fire extinguisher has charge, fire blanket in good condition etc)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Does the worker demonstrate an appropriate process to manage the hot work (e.g. fire checks, exclusion areas established etc)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op13->id, 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Logic Section (Manual Handling)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Manual Handling', 'description' => "Section 1i", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1i
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is the path of travel appropriate and unobstructed when carrying/placing/manually handling items?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Have suitable controls been put in place to address hazardous manual handling where possible? (use of mechnical lifting aids, rotating workers, arranging workflows to avoid peak physical and mental demands as appropriate - such as start and end of shifts)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op14->id, 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Logic Section (Noise)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Noise', 'description' => "Section 1j", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1j
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is appropriate hearing protection (PPE) supplied and being used correctly?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op15->id, 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Logic Section (Mobile Plant)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Mobile Plant', 'description' => "Section 1k", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1k
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Has a SWMS been prepared and provided to Cape Cod regarding any work in an area with movement of powered mobile plant?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is the mobile plant work zone isolated from workers and/or the public with physical barriers to minimise the risk of contact occurring between a person and the mobile plant? (exclusion zone established)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is the person operating the plant competent to do so? (such as where a High Risk Work Licence applies)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Has a method of communication been established between mobile plant operators and others?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Has a spotter been implemented where overhead powerlines are situated nearby the operation of mobile plant?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op16->id, 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Logic Section (Young / Vulnerable Workers)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Young / Vulnerable Workers', 'description' => "Section 1l", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1l
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Is appropriate Supervision available?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=*', 'match_value' => $op21->id, 'trigger' => 'section', 'trigger_id' => $sid2]);

        // Section 2 (Equipment)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Equipment', 'description' => "Section 2", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Electrical equipment including lead and plug connections in good physical condition?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Portable electrical equipment tested and tagged within 3 months and evidence of testing affixed to equipment by physical tag?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Portable Residual Current Devices (RCD) used?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Tooling & equipment safety guards in place as applicable and in suitable condition?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);


        //
        // Page 4 - Working at Height
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Working at Height", 'description' => null, 'order' => 4]);
        $pid = $page->id;
        $sn = 1;

        // Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Working at Height', 'description' => "Section 1", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Adequate edge protection installed to perimeters?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Penetrations, openings in floors/work surfaces suitably protected?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Fragile roof materials/floor surfaces (such as skylights, plastic roof sheets etc) suitably protected?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Guardrailing incorporates top-rail between 900 mm and 1100 mm above the working surface, a mid-rail and toeboards (except where it may be impractical to do so and alternative control measures, such as ‘no go’ zones, to ensure no persons are at risk of being hit by falling objects from the work above)", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Are scaffolds erected on the site?", 'type' => "select", 'type_special' => 'YN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $qid = $question->id;

        // Logic Section (Scaffolds)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => $sid, 'name' => 'Scaffolds', 'description' => "Section 1a", 'order' => $sn++]);
        $sid2 = $section->id;
        // Sub Section 1a
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Scaffold exceeding a deck height of 4m erected by a licensed scaffolder & handover certificate available?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Safe Work Load (SWL) not exceeded? (including weight of persons, tooling, materials etc)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1, 'helper' => '<img src="/img/form/scaffold_working_platforms.jpeg" class="img-responsive">']);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Scaffold complete (platform full width, handrail, toeboards and access to platforms compliant)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Has environmental loading (e.g. wind forces, rain)  been adequately addressed in the scaffold design? (sufficiently tied in, suitable containment material etc.)', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Gaps between the face of the building or structure and the erected scaffold do not exceed 225mm?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => '4 metre approach distance from overhead powerlines maintained in any direction where metallic scaffold is erected', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Electrical wires or apparatus that pass through a scaffold  de-energised or fully enclosed to the requirements of the network operator?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Special duty scaffolds (designed to a designated allowable load) and timber scaffolds exceeding 2m have been designed by a competent person and detailed design drawings kept?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid2,
            'name' => 'Edge protection provided at every open edge of the work platform?', 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $qid, 'match_operation' => '=', 'match_value' => '5', 'trigger' => 'section', 'trigger_id' => $sid2]);


        // Section 2 (Falling Objects)
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => 'Falling Objects', 'description' => "Section 2", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Suitable material/containment screens affixed to scaffold/roof rail/elevated work areas to arrest the free fall of objects to area below as applicable? (i.e. brick guard mesh, shadecloth etc)", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Have appropriate exclusion zones been established as applicable to address the risk of workers and others below being struck by any objects that may fall/be dropped/thrown from elevated work areas?", 'type' => "select", 'type_special' => 'CONN', 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);


        //
        // Page 5  (Summary)
        //
        $page = FormPage::create(['template_id' => $tid, 'name' => "Title Page", 'description' => null, 'order' => 5]);
        $pid = $page->id;

        // Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 1", 'order' => $sn++]);
        $sid = $section->id;
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Site Summary / Comments", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Worker Representative", 'type' => "select", 'type_special' => 'staff', 'type_version' => 'select2',
            'order' => $pn++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = FormQuestion::create(['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
            'name' => "Signature of Inspector", 'type' => "select", 'type_special' => 'staff', 'type_version' => 'select2',
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
                $parent_section = ($section->parent) ? $match_sections[$section->parent] : null;
                $s = FormSection::create(['template_id' => $t->id, 'page_id' => $p->id, 'parent' => $parent_section, 'name' => $section->name, 'description' => $section->description, 'order' => $section->order]);
                $match_sections[$section->id] = $s->id;
                // Questions
                foreach ($section->questions as $question) {
                    $q = FormQuestion::create(['template_id' => $t->id, 'page_id' => $p->id, 'section_id' => $s->id, 'name' => $question->name, 'type' => $question->type, 'type_special' => $question->type_special, 'type_version' => $question->type_version,
                        'order' => $question->order, 'default' => $question->default, 'multiple' => $question->multiple, 'required' => $question->required, 'helper' => $question->helper]);
                    $match_questions[$question->id] = $q->id;
                    // Question Options
                    foreach ($question->options() as $option) {
                        // Only create 'custom' options ie exclude master options ie (CONN, YN, YrY, YgN, YgNr)
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
            if ($question->type == 'select' && !in_array($question->type_special, ['site', 'staff', 'CONN', 'YN', 'YrN', 'YgN', 'YgNr', 'YNNA'])) {
                $old_match_value = explode(',', str_replace(' ', '', $logic->match_value));
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
                $section_type = ($section->parent) ? "Sub-Section" : "Section";
                echo "$section_type $section->order : $section->name (pid:" . $section->page->id . " sid:$section->id  psid:$section->parent)<br>-------------------------------------<br>";
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

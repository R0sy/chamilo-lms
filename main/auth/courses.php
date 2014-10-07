<?php
/* For licensing terms, see /license.txt */
/**
* Template (front controller in MVC pattern) used for distpaching to the controllers depend on the current action
* @author Christian Fasanando <christian1827@gmail.com> - Beeznest
* @package chamilo.auth
*/
/**
 * Code
 */
// Names of the language file that needs to be included.
$language_file = array ('courses', 'registration');

// Delete the globals['_cid'], we don't need it here.
$cidReset = true; // Flag forcing the 'current course' reset

// including files
require_once '../inc/global.inc.php';

$ctok = $_SESSION['sec_token'];

require_once api_get_path(LIBRARY_PATH).'auth.lib.php';
require_once api_get_path(LIBRARY_PATH).'app_view.php';
require_once 'courses_controller.php';
require_once api_get_path(LIBRARY_PATH).'mail.lib.inc.php';

if (api_get_setting('show_courses_descriptions_in_catalog') == 'true') {
    $htmlHeadXtra[] = '
    <script>
    	$(document).ready(function() {
	        $(\'.ajax\').live(\'click\', function() {
	            var url     = this.href;
	            var dialog  = $("#dialog");
	            if ($("#dialog").length == 0) {
	                dialog  = $(\'<div id="dialog" style="display:hidden"></div>\').appendTo(\'body\');
	            }

	            // load remote content
	            dialog.load(
	                    url,
	                    {},
	                    function(responseText, textStatus, XMLHttpRequest) {
	                        dialog.dialog({
	                            modal	: true,
	            				width	: 540,
	            				height	: 400,
	                        });
				});
	            //prevent the browser to follow the link
	            return false;
	        });
        });

    </script>';
}


// Section for the tabs.
$this_section = SECTION_COURSES;

// Access rights: anonymous users can't do anything useful here.
api_block_anonymous_users();

$user_can_view_page = false;

//For students
if (api_get_setting('allow_students_to_browse_courses') == 'false') {
    $user_can_view_page = false;
} else {
    $user_can_view_page = true;
}

//For teachers/admins
if (api_is_platform_admin() || api_is_course_admin() || api_is_allowed_to_create_course()) {
    $user_can_view_page = true;
}

// filter actions
$actions = array('sortmycourses', 'createcoursecategory', 'subscribe', 'deletecoursecategory', 'display_courses', 'display_random_courses', 'subscribe_user_with_password', 'display_sessions');
$action = CoursesAndSessionsCatalog::is(CATALOG_SESSIONS) ? 'display_sessions' : 'display_random_courses';
$nameTools = get_lang('SortMyCourses');

if (isset($_GET['action']) && in_array($_GET['action'],$actions)) {
	$action = $_GET['action'];
}

if ($action == 'createcoursecategory') {
	$nameTools = get_lang('CreateCourseCategory');
}
if ($action == 'subscribe') {
	$nameTools = get_lang('CourseManagement');
}

if ($action == 'subscribe_user_with_password') {
	$nameTools = get_lang('CourseManagement');
}

if ($action == 'display_random_courses' || $action == 'display_courses' ) {
	$nameTools = get_lang('CourseManagement');
}

if ($action == 'display_sessions') {
    $nameTools = get_lang('Sessions');
}

// Breadcrumbs.
$interbreadcrumb[] = array('url' => api_get_path(WEB_PATH).'user_portal.php', 'name' => get_lang('MyCourses'));

if (empty($nameTools)) {
	$nameTools = get_lang('CourseManagement');
} else {

	if (!in_array($action, array('sortmycourses', 'createcoursecategory', 'display_random_courses', 'display_courses', 'subscribe'))) {
		$interbreadcrumb[] = array('url' => api_get_path(WEB_CODE_PATH).'auth/courses.php', 'name' => get_lang('CourseManagement'));
	}
	if ($action == 'createcoursecategory') {
		$interbreadcrumb[] = array('url' => api_get_path(WEB_CODE_PATH).'auth/courses.php?action=sortmycourses', 'name' => get_lang('SortMyCourses'));
	}
    $interbreadcrumb[] = array('url' => '#', 'name' => $nameTools);
}

// course description controller object
$courses_controller = new CoursesController();

// We are moving a course or category of the user up/down the list (=Sort My Courses).
if (isset($_GET['move'])) {
	if (isset($_GET['course'])) {
		if ($ctok == $_GET['sec_token']) {
            $courses_controller->move_course($_GET['move'], $_GET['course'], $_GET['category']);
		}
	}
	if (isset($_GET['category']) && !$_GET['course']) {
		if ($ctok == $_GET['sec_token']) {
            $courses_controller->move_category($_GET['move'], $_GET['category']);
		}
	}
}

// We are moving the course of the user to a different user defined course category (=Sort My Courses).
if (isset($_POST['submit_change_course_category'])) {
    if ($ctok == $_POST['sec_token']) {
        $courses_controller->change_course_category($_POST['course_2_edit_category'], $_POST['course_categories']);
    }
}

// We edit course category
if (isset($_POST['submit_edit_course_category']) && isset($_POST['title_course_category']) && strlen(trim($_POST['title_course_category'])) > 0) {
	if ($ctok == $_POST['sec_token']) {
		$courses_controller->edit_course_category($_POST['title_course_category'], $_POST['edit_course_category']);
	}
}

// we are deleting a course category
if ($action == 'deletecoursecategory' && isset($_GET['id'])) {
	if ($ctok == $_GET['sec_token']) {
		$get_id_cat = intval($_GET['id']);
		$courses_controller->delete_course_category($get_id_cat);
	}
}

// We are creating a new user defined course category (= Create Course Category).
if (isset($_POST['create_course_category']) && isset($_POST['title_course_category']) && strlen(trim($_POST['title_course_category'])) > 0) {
	if ($ctok == $_POST['sec_token']) {
        $courses_controller->add_course_category($_POST['title_course_category']);
	}
}

// search courses
if (isset($_REQUEST['search_course'])) {
    //echo "<p><strong>".get_lang('SearchResultsFor')." ".api_htmlentities($_POST['search_term'], ENT_QUOTES, api_get_system_encoding())."</strong><br />";
    if ($ctok == $_REQUEST['sec_token']) {
        $courses_controller->search_courses($_REQUEST['search_term']);
    }
}

// Subscribe user to course
if (isset($_REQUEST['subscribe_course'])) {
    if ($ctok == $_GET['sec_token']) {
        $courses_controller->subscribe_user($_GET['subscribe_course'], $_GET['search_term'], $_GET['category_code']);
    }
}
// We are unsubscribing from a course (=Unsubscribe from course).
if (isset($_GET['unsubscribe'])) {
	if ($ctok == $_GET['sec_token']) {
        $courses_controller->unsubscribe_user_from_course($_GET['unsubscribe'], $_GET['search_term'], $_GET['category_code']);
            //$message = remove_user_from_course($_user['user_id'], $_POST['unsubscribe']);
	}
}
// We are unsubscribing from a course (=Unsubscribe from course).
if (isset($_POST['unsubscribe'])) {
	if ($ctok == $_POST['sec_token']) {
        $courses_controller->unsubscribe_user_from_course($_POST['unsubscribe']);
            //$message = remove_user_from_course($_user['user_id'], $_POST['unsubscribe']);
	}
}

switch ($action) {
    case 'subscribe_user_with_password':
        $courses_controller->subscribe_user($_POST['subscribe_user_with_password'], $_POST['search_term'], $_POST['category_code']);
        exit;
        break;
    case 'createcoursecategory':
        $courses_controller->categories_list($action);
        break;
    case 'deletecoursecategory':
        $courses_controller->courses_list($action);
        break;
    case 'sortmycourses':
        $courses_controller->courses_list($action);
        break;
    case 'subscribe':
    case 'display_random_courses':
        if ($user_can_view_page) {
            $courses_controller->courses_categories($action);
        } else {
            api_not_allowed();
        }
        break;
    case 'display_courses':
        $courses_controller->courses_categories($action, $_GET['category_code']);
        break;
    case 'display_sessions':
        $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
        $hiddenLinks = intval($_GET['hidden_links']) == 1;

        $userInfo = api_get_user_info();
        $allowEmailEditor = api_get_setting('allow_email_editor') === 'true';
        $administratorEmail = api_get_setting('emailAdministrator');

        $authModel = new Auth();
        $sessions = $authModel->browseSessions($date);
        $courseCategories = $authModel->browse_course_categories();

        $sessionsBlocks = array();

        foreach ($sessions as $session) {
            $sessionsBlocks[] = array(
                'id' => $session['id'],
                'name' => $session['name'],
                'nbr_courses' => $session['nbr_courses'],
                'nbr_users' => $session['nbr_users'],
                'coach_name' => $session['coach_name'],
                'is_subscribed' => $session['is_subscribed'],
                'icon' => $courses_controller->getSessionIcon($session['name']),
                'date' => SessionManager::getSessionFormattedDate($session),
                'subscribe_button' => $courses_controller->getRegisterInSessionButton($session['name'], $userInfo, $administratorEmail, $allowEmailEditor)
            );
        }

        $tpl = new Template();
        $tpl->assign('action', $action);
        $tpl->assign('showCourses', CoursesAndSessionsCatalog::showCourses());
        $tpl->assign('showSessions', CoursesAndSessionsCatalog::showSessions());
        $tpl->assign('api_get_self', api_get_self());

        $tpl->assign('coursesCategoriesList', $courses_controller->getCoursesCategoriesBlock());

        $tpl->assign('texts', array(
            'search' => get_lang('Search'),
            'randomPick' => get_lang('RandomPick'),
            'courseCategories' => get_lang('CourseCategories'),
            'courseList' => get_lang('CourseList'),
            'sessions' => get_lang('Sessions'),
            'sessionList' => $nameTools,
            'searchSessions' => get_lang('SearchSessions')
        ));

        $tpl->assign('hiddenLinks', $hiddenLinks);
        $tpl->assign('searchToken', Security::get_token());

        $tpl->assign('searchDate', $date);
        $tpl->assign('web_session_courses_ajax_url', api_get_path(WEB_AJAX_PATH) . 'course.ajax.php');
        $tpl->assign('sessions_blocks', $sessionsBlocks);
        $tpl->assign('already_subscribed_label', $courses_controller->getAlreadyRegisterInSessionLabel());

        $conentTemplate = $tpl->get_template('auth/sessions_catalog.tpl');

        $tpl->display($conentTemplate);
        break;
}

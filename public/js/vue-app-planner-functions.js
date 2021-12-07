Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');

$.ajaxSetup({
    headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
});

var host = window.location.hostname;
var dev = true;
if (host == 'safeworksite.com.au')
    dev = false;

Vue.component('select-picker', {
    template: '<select v-model="name" class="form-control" @change="function">' +
    '<option v-for="option in options" value="{{ option.value }}">{{{ option.text }}}</option>' +
    '</select>',
    name: 'selectpicker',
    props: ['options', 'name', 'function'],
    ready: function () {
        // Init our picker
        $(this.$el).selectpicker({
            iconBase: 'fa',
            tickIcon: 'fa-check'
        });
        // Update whenever options change
        this.$watch('options', function (val) {
            // Refresh our picker UI
            $(this.$el).selectpicker('refresh');
            // Update manually because v-model won't catch
            this.name = $(this.$el).selectpicker('val');
        }.bind(this))
    }
});


// Post data to url via POST method
function postAndRedirect(url, postData) {
    var postFormStr = "<form method='POST' action='" + url + "'>\n";

    for (var key in postData) {
        if (postData.hasOwnProperty(key))
            postFormStr += "<input type='hidden' name='" + key + "' value='" + postData[key] + "'></input>";
    }

    postFormStr += "</form>";
    var formElement = $(postFormStr);

    $('body').append(formElement);
    $(formElement).submit();
}

// Search through array of object with given 'key' and 'value'
function objectFindByKey(array, key, value) {
    for (var i = 0; i < array.length; i++) {
        if (array[i][key] == value) {
            return array[i];
        }
    }
    return null;
}

// Sort Entity by Key
function sortEntityKey(a, b) {
    if (a.key < b.key)
        return -1;
    if (a.key > b.key)
        return 1;
    return 0;
}
// Sort Entity by Name
function sortEntityName(a, b) {
    if (a.entity_name < b.entity_name)
        return -1;
    if (a.entity_name > b.entity_name)
        return 1;
    return 0;
}

// Get all tasks for given date and return array
function tasksOnDate(plan, date) {
    var day_plan = [];
    // Search Site Plan for all tasks on specific date
    for (var i = 0; i < plan.length; i++) {
        var from = moment(plan[i]['from']).format('YYYY-MM-DD');
        var to = moment(plan[i]['to']).format('YYYY-MM-DD');
        // Allow tasks that span multiple days
        if (moment(date).isBetween(from, to, null, '[]'))
            day_plan.push(plan[i]);
    }
    return day_plan;
}

// Get attendance for Company on certain Date
function attendanceOnDate(attendance, date, etype, eid) {
    var attendees = '';
    // Search Attendance for entity on specific date
    for (var i = 0; i < attendance.length; i++) {
        var attend = attendance[i];
        if (attend.date == date && etype == 'c' && attend.company_id == eid) {
            if (attendees)
                attendees = attendees + ', ' + attend.user_name + ' (' + attend.time + ')';
            else
                attendees = attend.user_name + ' (' + attend.time + ')';
        }
    }
    return attendees;
}

// Get roster for Company on certain Date
function rosterOnDate(roster, date, etype, eid) {
    var rostered = '';
    // Search Attendance for entity on specific date
    for (var i = 0; i < roster.length; i++) {
        var r = roster[i];
        if (r.date == date && etype == 'c' && r.company_id == eid) {
            if (rostered)
                rostered = rostered + ', ' + r.user_name + ' (' + r.time + ')';
            else
                rostered = r.user_name + ' (' + r.time + ')';
        }
    }
    return rostered;
}

// Determine next 'work' day ie mon-fri (x) days from given date
// either before (-) or after (+) given date
function nextWorkDate(date, direction, days) {
    var newDate = moment(date);
    for (var i = 0; i < days; i++) {
        if (direction === '+') {
            newDate = moment(newDate).add(1, 'days');
            if (newDate.day() === 6) // Skip Sat
                newDate = moment(newDate).add(2, 'days');
            if (newDate.day() === 0) // Skip Sun
                newDate = moment(newDate).add(1, 'days');
        } else {
            newDate = moment(newDate).subtract(1, 'days');
            if (newDate.day() === 6) // skip Sat
                newDate = moment(newDate).subtract(1, 'days');
            if (newDate.day() === 0) // skip Sun
                newDate = moment(newDate).subtract(2, 'days');
        }
    }

    return newDate.format('YYYY-MM-DD');
}

// Determine number of 'work' days ie mon-fri
// between 2 dates (inclusive of from, to dates)
function workDaysBetween(from, to) {
    if (moment(from).isBefore(to)) {
        var startDate = moment(from);
        var endDate = moment(to);
    } else {
        var startDate = moment(to);
        var endDate = moment(from);
    }

    var counter = 0;

    while (startDate.format('YYYY-MM-DD') != endDate.format('YYYY-MM-DD')) {
        if (startDate.day() > 0 && startDate.day() < 6) {
            counter++;
            startDate.add(1, 'days');
        } else if (startDate.day() === 6) { // Skip Sat
            startDate.add(1, 'days');
        } else if (startDate.day() === 0) { // Skip Sun
            startDate.add(1, 'days');
        }
    }
    if (endDate.day() > 0 && endDate.day() < 6)
        counter++;

    return counter;
}

// Get Connected Tasks from a given date
// connected tasks are multiple tasks by an Entity on the same day or
// a number of tasks the following 'work days' with no 'day off' in between.
function connectedTasks(plan, site_id, etype, eid, date) {
    var connected_tasks = [];
    var current_date = moment(date);
    var stop = false;

    // Loop through plan and find connected tasks
    for (var x = 0; x < plan.length; x++) {
        var found_date = false;
        for (var i = 0; i < plan.length; i++) {
            var task = this.xx.plan[i];
            if (task.site_id == site_id && task.entity_type == etype && task.entity_id == eid) {
                // Allow tasks that span multiple days
                if (current_date.isBetween(task.from, task.to, null, '[]')) {
                    // Only add task to string if not already present
                    if (!objectFindByKey(connected_tasks, 'id', task.id))
                        connected_tasks.push(task);
                    found_date = true;
                }
            }
        }
        if (!found_date)
            break;

        current_date = moment(nextWorkDate(current_date.format('YYYY-MM-DD'), '+', 1));
    }
    return connected_tasks;
}

// Update given task 'to' date detemined by 'from' date + number of task 'days'
// return a 'promise'
function updateTaskToDate(task) {
    return new Promise(function (resolve, reject) {
        var originalDate = task.to;
        var currentDate = moment(new Date(task.from));
        for (var i = 1; i < task.days; i++) {
            currentDate.add(1, 'days');
            if (currentDate.day() === 6)
                currentDate.add(2, 'days');
        }
        task.to = currentDate.format('YYYY-MM-DD');

        // Update task in DB and once done fulfil the 'promise'
        updateTaskDB(task).then(function (result) {
            if (result) {
                // Update global start or start_carp dates if required
                if (task.task_code === 'START') this.xx.start_date = task.from;
                if (task.task_code === 'STARTCarp') this.xx.start_carp = task.from;
                if (task.task_id == '5') this.xx.carp_prac = task.from;
                console.log('updated task TO:' + task.task_name + ' T:' + originalDate + ' -> ' + task.to);
                resolve(task);
            } else
                reject(false);
        }.bind(this));
    });
}

// Split Task into 2 from given date and return the part2 'after' date portion
// The part1 'before' date portion ends the day prior to given date but retains the original 'id'
// The part2 'after' date portion is provided with a new 'id' from database
function splitTaskFromDate(plan, task, date) {
    return new Promise(function (resolve, reject) {
        console.log('spliting task:[' + task.id + '] ' + task.task_name + ' F:' + task.from + ' T:' + task.to + ' days:' + task.days);
        var splitTask = jQuery.extend({}, task) // copy task

        task.to = nextWorkDate(date, '-', 1);
        task.days = workDaysBetween(task.from, task.to);
        console.log('split p1:[' + task.id + '] ' + task.task_name + ' F:' + task.from + ' T:' + task.to + ' days:' + task.days);

        // The first portion maintains the task id in DB so update the new 'end' date
        updateTaskDB(task).then(function (result) {
            if (result) {
                splitTask.from = date;
                splitTask.days = splitTask.days - task.days;
                addTaskDB(splitTask).then(function (result) { // the second portion p2 is a new entry in the database
                    if (result) {
                        splitTask.id = result.id;  // The second portion gets a new task id
                        toastr.warning('Task ' + task.task_name + ' was split as it began before specified date ' + moment(date).format('DD/MM/Y'));
                        console.log('split p2:[' + splitTask.id + '] ' + splitTask.task_name + ' F:' + splitTask.from + ' T:' + splitTask.to + ' days:' + splitTask.days);
                        console.log('split p1[' + task.id + '] p2[' + splitTask.id + ']');

                        plan.push(splitTask); // put new task p2 onto planner
                        resolve(splitTask);
                    } else
                        reject(false);
                });
            } else {
                console.log('task updated failed');
                reject(false);
            }
        });
    });
}

// Move task 'x' days in given direction forward '+' or backwards '-'
//  - if moving task forward and it spans multiple days but given date is not the
//    task 'from' date then split the task in 2 and move the second half only.
function moveTaskFromDate(plan, task, date, direction, days) {
    return new Promise(function (resolve, reject) {
        console.log('moving task:[' + task.id + '] ' + task.task_name + ' D:' + date + ' ' + direction + days + ' days');
        // Check if in middle of a string - ie. task that span multiple days
        // Allow tasks that span multiple days
        if (direction === '+' && moment(date).isBetween(task.from, task.to, null, '(]')) {
            splitTaskFromDate(plan, task, date).then(function (splitTask) {
                if (splitTask) {
                    // Change current task to start from given date + 'x' days forward
                    var newDate = nextWorkDate(date, direction, days);
                    var oldDate = task.from;
                    console.log('move split task2[' + splitTask.id + '] to new date[' + newDate + ']');

                    splitTask.from = newDate;
                    updateTaskToDate(splitTask).then(function (result) {
                        if (result)
                            resolve(splitTask);
                        else {
                            splitTask.from = oldDate;
                            reject(false);
                        }

                    });
                } else
                    reject(false);
            });
        } else {
            // Move Task x days
            var newDate = nextWorkDate(task.from, direction, days);
            var oldDate = task.from;

            // Prevent task being moved to or before today for Active/Maintenance sites
            if (moment(newDate).isSameOrBefore(moment(), 'day') && task.site_status > 0) {
                toastr.error('Unable to move task to or before today.');
                reject(false);
            } else {
                task.from = newDate;
                updateTaskToDate(task).then(function (result) {
                    if (result)
                        resolve(task);
                    else {
                        task.from = oldDate;
                        reject(false);
                    }
                });
            }
        }
    });
}

// Move whole job from given date 'x' days in given direction forward '+' or backwards '-'
function moveJobFromDate(plan, site_id, date, direction, days) {
    return new Promise(function (resolve, reject) {
        console.log('move job: D:' + date + ' ' + direction + days + 'days');
        var promises = [];
        for (var i = 0; i < plan.length; i++) {
            //var task = jQuery.extend({}, plan[i]);
            var task = plan[i];
            // Move all tasks after given date
            if (task.site_id == site_id && (moment(task.from).isSameOrAfter(date, 'day') || moment(task.to).isSameOrAfter(date, 'day'))) {
                var promise = moveTaskFromDate(plan, task, date, direction, days);
                promises.push(promise);
            }
        }
        Promise.all(promises).then(resolve);
    });
}

// Move Entity and connected tasks from given date 'x' days in given direction forward '+' or backwards '-'
function moveEntityFromDate(plan, connected_tasks, date, direction, days) {
    return new Promise(function (resolve, reject) {
        console.log('move entity: D:' + date + ' ' + direction + days + 'days');
        var promises = [];
        for (var i = 0; i < plan.length; i++) {
            //var task = jQuery.extend({}, plan[i]);
            var task = plan[i];
            // Move all tasks after given date
            if (objectFindByKey(connected_tasks, 'id', task.id)) {
                var promise = moveTaskFromDate(plan, task, date, direction, days);
                promises.push(promise);
            }
        }
        Promise.all(promises).then(resolve);
    });
}

// Move task to given date
function moveTasktoDate(plan, task, date) {
    return new Promise(function (resolve, reject) {
        console.log('moving task:' + task.task_name + ' to D:' + date);
        // Prevent task being moved to or before today for Active/Maintenance sites
        if (moment(date).isSameOrBefore(moment(), 'day') && task.site_status > 0) {
            toastr.error('Unable to move task "' + task.task_name + '" to or before today.');
            reject(false);
        } else {
            task.from = date;
            updateTaskToDate(task).then(function (result) {
                if (result) {
                    // Add task to current Planner if not present
                    var result = objectFindByKey(plan, 'id', task.id);
                    if (result) {
                        result.from = task.from;
                        result.to = task.to;
                    } else
                        plan.push(task);
                    resolve(task);
                } else
                    reject(false);
            });
        }
    });
}


// Assign a task to a company
function assignTask(task, etype, eid, ename) {
    return new Promise(function (resolve, reject) {
        task.entity_type = etype;
        task.entity_id = eid;
        task.entity_name = ename;
        console.log('assigned task id:[' + task.id + '] name:' + ename + ' to entity type:' + etype + ' id:' + eid);
        updateTaskDB(task).then(function (result) {
            if (result) {
                resolve(true);
            } else {
                console.log('assign task failed');
                reject(false);
            }
        });
    });
}

// Assign all tasks match 'trade_id' from given 'date' to company
function assignTasksFromDate(plan, site_id, etype, eid, ename, task_amount, trade_id, date) {
    return new Promise(function (resolve, reject) {
        console.log('assigning ' + task_amount + ' tasks for trade ' + trade_id + ' to entity type:' + etype + ' id:' + eid + ' date:' + date);
        var promises = [];
        for (var i = 0; i < plan.length; i++) {
            var task = plan[i];
            // Check if current task matches given trade_id
            if (task.site_id == site_id && task.trade_id == trade_id) {
                // Check if task starts or ends is same or after given date
                if (moment(task.from).isSameOrAfter(date, 'day') || moment(task.to).isSameOrAfter(date, 'day')) {
                    // Prevent assigning any tasks that start before or on today
                    if (moment(task.from).isSameOrBefore(moment(), 'day')) {
                        var promise = splitTaskFromDate(plan, task, date).then(function (splitTask) {
                            if (splitTask) {
                                var promise2 = assignTask(splitTask, etype, eid, ename);
                                promises.push(promise2);
                            }
                        });
                    } else {
                        // Assign 'current day' only tasks or 'every' task after given date
                        if (task_amount == 'all' || (task_amount == 'day' && moment(date).isBetween(task.from, task.to, null, '[]')))
                            var promise = assignTask(task, etype, eid, ename);
                    }
                    promises.push(promise);
                }
            }
        }

        Promise.all(promises).then(resolve);
    });
}

// Add Start task to planner + all the associated tasks with it
function addStartTaskToPlanner(plan, site_id, date) {
    console.log('adding Job START + associated tasks to planner')

    // 5 days prior
    var preConst_date = nextWorkDate(date, '-', 5);
    if (moment(preConst_date).isSameOrBefore(moment(), 'day'))
        var preConst_date = nextWorkDate(moment().format('YYYY-MM-DD'), '+', 1);
    var preConst = {
        id: '', site_id: site_id, entity_type: 't', entity_id: 31, entity_name: 'Supervisors', task_id: 264, task_code: 'Pre',
        task_name: 'Pre Construction', from: preConst_date, to: preConst_date, days: 1
    };

    // Same Day
    var startJob = {
        id: '', site_id: site_id, entity_type: 't', entity_id: 2, entity_name: 'Carpenter', task_id: 11, task_code: 'START', task_name: 'Start Job', from: date, to: date, days: 1
    };
    var loadJob = {
        id: '', site_id: site_id, entity_type: 't', entity_id: 21, entity_name: 'Labourer', task_id: 200, task_code: 'Load', task_name: 'Load Job', from: date, to: date, days: 1
    };
    var errectScaff = {
        id: '', site_id: site_id, entity_type: 'c', entity_id: 9, entity_name: 'Ashbys Scaffolding', task_id: 116, task_code: 'E', task_name: 'Erect Scaffold', from: date, to: date, days: 1
    };
    var roofMaint = {
        id: '', site_id: site_id, entity_type: 'c', entity_id: 118, entity_name: 'Roofworx', task_id: 107, task_code: 'Maint', task_name: 'Roof Maintenance', from: date, to: date, days: 1
    };

    // 1 day after
    var startCarp = {
        id: '', site_id: site_id, entity_type: 't', entity_id: 2, entity_name: 'Carpenter', task_id: 22, task_code: 'STARTCarp',
        task_name: 'Start Carpentry', from: nextWorkDate(date, '+', 1), to: nextWorkDate(date, '+', 1), days: 1
    };
    // 2 days after
    var layFloor = {
        id: '', site_id: site_id, entity_type: 't', entity_id: 2, entity_name: 'Carpenter', task_id: 4, task_code: 'LF',
        task_name: 'Lay Floor', from: nextWorkDate(date, '+', 2), to: nextWorkDate(date, '+', 5), days: 4
    };
    var electDriveby = {
        id: '', site_id: site_id, entity_type: 't', entity_id: 4, entity_name: 'Electrician', task_id: 51, task_code: 'DB',
        task_name: 'Drive By', from: nextWorkDate(date, '+', 2), to: nextWorkDate(date, '+', 2), days: 1
    };
    var plumbDriveby = {
        id: '', site_id: site_id, entity_type: 't', entity_id: 8, entity_name: 'Plumber', task_id: 86, task_code: 'DB',
        task_name: 'Drive By', from: nextWorkDate(date, '+', 2), to: nextWorkDate(date, '+', 2), days: 1
    };
    // 4 days after
    var floorInspect = {
        id: '', site_id: site_id, entity_type: 'c', entity_id: 23, entity_name: 'Essential Certifiers', task_id: 183, task_code: 'Fl',
        task_name: 'Floor Inspection', from: nextWorkDate(date, '+', 4), to: nextWorkDate(date, '+', 4), days: 1
    };
    // 5 days after
    var frameRoof = {site_id: site_id, entity_type: 't', entity_id: 2, task_id: 7, from: nextWorkDate(date, '+', 5), to: nextWorkDate(date, '+', 8), days: 4};
    // 7 days after
    var loadPlatform = {site_id: site_id, entity_type: 't', entity_id: 21, task_id: 224, from: nextWorkDate(date, '+', 7), to: nextWorkDate(date, '+', 7), days: 1};
    // 8 days after
    var platformUpLab = {site_id: site_id, entity_type: 't', entity_id: 21, task_id: 220, from: nextWorkDate(date, '+', 8), to: nextWorkDate(date, '+', 8), days: 1};
    var platformUpCarp = {site_id: site_id, entity_type: 't', entity_id: 2, task_id: 24, from: nextWorkDate(date, '+', 8), to: nextWorkDate(date, '+', 8), days: 1};
    // 9 days after
    var fasciaGutter = {site_id: site_id, entity_type: 't', entity_id: 20, task_id: 191, from: nextWorkDate(date, '+', 9), to: nextWorkDate(date, '+', 9), days: 1};
    // 10 days after
    var floorCover = {site_id: site_id, entity_type: 't', entity_id: 9, task_id: 100, from: nextWorkDate(date, '+', 10), to: nextWorkDate(date, '+', 10), days: 1};
    // 11 days after
    var pointing = {site_id: site_id, entity_type: 't', entity_id: 9, task_id: 108, from: nextWorkDate(date, '+', 11), to: nextWorkDate(date, '+', 11), days: 1};
    // 12 days after
    var platformDnLab = {site_id: site_id, entity_type: 't', entity_id: 21, task_id: 221, from: nextWorkDate(date, '+', 12), to: nextWorkDate(date, '+', 12), days: 1};
    var platformDnCarp = {site_id: site_id, entity_type: 't', entity_id: 2, task_id: 25, from: nextWorkDate(date, '+', 12), to: nextWorkDate(date, '+', 12), days: 1};
    var polEaves = {site_id: site_id, entity_type: 't', entity_id: 2, task_id: 10, from: nextWorkDate(date, '+', 12), to: nextWorkDate(date, '+', 13), days: 2};
    var catwalkUp = {site_id: site_id, entity_type: 't', entity_id: 2, task_id: 27, from: nextWorkDate(date, '+', 12), to: nextWorkDate(date, '+', 12), days: 1};
    // 13 days after
    var frameInspect = {site_id: site_id, entity_type: 'c', entity_id: 23, task_id: 184, from: nextWorkDate(date, '+', 13), to: nextWorkDate(date, '+', 13), days: 1};
    // 14 days after
    var genClean = {site_id: site_id, entity_type: 't', entity_id: 21, task_id: 198, from: nextWorkDate(date, '+', 14), to: nextWorkDate(date, '+', 14), days: 1};

    addTaskToPlanner(plan, preConst);
    addTaskToPlanner(plan, startJob);
    addTaskToPlanner(plan, loadJob);
    addTaskToPlanner(plan, errectScaff);
    addTaskToPlanner(plan, roofMaint);
    addTaskToPlanner(plan, startCarp);
    addTaskToPlanner(plan, layFloor);
    addTaskToPlanner(plan, electDriveby);
    addTaskToPlanner(plan, plumbDriveby);
    addTaskToPlanner(plan, floorInspect);
    addTaskToPlanner(plan, frameRoof);
    addTaskToPlanner(plan, loadPlatform);
    addTaskToPlanner(plan, platformUpLab);
    addTaskToPlanner(plan, platformUpCarp);
    addTaskToPlanner(plan, fasciaGutter);
    addTaskToPlanner(plan, floorCover);
    addTaskToPlanner(plan, pointing);
    addTaskToPlanner(plan, platformDnLab);
    addTaskToPlanner(plan, platformDnCarp);
    addTaskToPlanner(plan, polEaves);
    addTaskToPlanner(plan, catwalkUp);
    addTaskToPlanner(plan, frameInspect);
    addTaskToPlanner(plan, genClean);


    // Email Jobstart
    var data = {site_id: site_id, newdate: date};
    $.ajax({
        url: '/planner/data/trade/email-jobstart',
        type: 'POST',
        data: data,
        success: function (result) {
            console.log('Emailed Jobstart');
        },
        error: function (result) {
            alert("Failed to email Job Start");
            console.log('Email of Job Start FAILED');
        }
    });
}

// Add Start task to planner + all the associated tasks with it
function addStartTaskToPlanner2(plan, site_id, date) {

    return new Promise(function (resolve, reject) {
        console.log('adding Job START + associated tasks to planner')

        // 5 days prior
        var preConst_date = nextWorkDate(date, '-', 5);
        if (moment(preConst_date).isSameOrBefore(moment(), 'day'))
            var preConst_date = nextWorkDate(moment().format('YYYY-MM-DD'), '+', 1);
        var preConst = {
            id: '', site_id: site_id, entity_type: 't', entity_id: 31, entity_name: 'Supervisors', task_id: 264, task_code: 'Pre',
            task_name: 'Pre Construction', from: preConst_date, to: preConst_date, days: 1
        };

        // Same Day
        var startJob = {
            id: '', site_id: site_id, entity_type: 't', entity_id: 2, entity_name: 'Carpenter', task_id: 11, task_code: 'START', task_name: 'Start Job', from: date, to: date, days: 1
        };
        var loadJob = {
            id: '', site_id: site_id, entity_type: 't', entity_id: 21, entity_name: 'Labourer', task_id: 200, task_code: 'Load', task_name: 'Load Job', from: date, to: date, days: 1
        };
        var errectScaff = {
            id: '', site_id: site_id, entity_type: 'c', entity_id: 9, entity_name: 'Ashbys Scaffolding', task_id: 116, task_code: 'E', task_name: 'Erect Scaffold', from: date, to: date, days: 1
        };
        var roofMaint = {
            id: '', site_id: site_id, entity_type: 'c', entity_id: 118, entity_name: 'Roofworx', task_id: 107, task_code: 'Maint', task_name: 'Roof Maintenance', from: date, to: date, days: 1
        };

        // 1 day after
        var startCarp = {
            id: '', site_id: site_id, entity_type: 't', entity_id: 2, entity_name: 'Carpenter', task_id: 22, task_code: 'STARTCarp',
            task_name: 'Start Carpentry', from: nextWorkDate(date, '+', 1), to: nextWorkDate(date, '+', 1), days: 1
        };
        // 2 days after
        var layFloor = {
            id: '', site_id: site_id, entity_type: 't', entity_id: 2, entity_name: 'Carpenter', task_id: 4, task_code: 'LF',
            task_name: 'Lay Floor', from: nextWorkDate(date, '+', 2), to: nextWorkDate(date, '+', 5), days: 4
        };
        var electDriveby = {
            id: '', site_id: site_id, entity_type: 't', entity_id: 4, entity_name: 'Electrician', task_id: 51, task_code: 'DB',
            task_name: 'Drive By', from: nextWorkDate(date, '+', 2), to: nextWorkDate(date, '+', 2), days: 1
        };
        var plumbDriveby = {
            id: '', site_id: site_id, entity_type: 't', entity_id: 8, entity_name: 'Plumber', task_id: 86, task_code: 'DB',
            task_name: 'Drive By', from: nextWorkDate(date, '+', 2), to: nextWorkDate(date, '+', 2), days: 1
        };
        // 4 days after
        var floorInspect = {
            id: '', site_id: site_id, entity_type: 'c', entity_id: 23, entity_name: 'Essential Certifiers', task_id: 183, task_code: 'Fl',
            task_name: 'Floor Inspection', from: nextWorkDate(date, '+', 4), to: nextWorkDate(date, '+', 4), days: 1
        };
        // 5 days after
        var frameRoof = {site_id: site_id, entity_type: 't', entity_id: 2, task_id: 7, from: nextWorkDate(date, '+', 5), to: nextWorkDate(date, '+', 8), days: 4};
        // 7 days after
        var loadPlatform = {site_id: site_id, entity_type: 't', entity_id: 21, task_id: 224, from: nextWorkDate(date, '+', 7), to: nextWorkDate(date, '+', 7), days: 1};
        // 8 days after
        var platformUpLab = {site_id: site_id, entity_type: 't', entity_id: 21, task_id: 220, from: nextWorkDate(date, '+', 8), to: nextWorkDate(date, '+', 8), days: 1};
        var platformUpCarp = {site_id: site_id, entity_type: 't', entity_id: 2, task_id: 24, from: nextWorkDate(date, '+', 8), to: nextWorkDate(date, '+', 8), days: 1};
        // 9 days after
        var fasciaGutter = {site_id: site_id, entity_type: 't', entity_id: 20, task_id: 191, from: nextWorkDate(date, '+', 9), to: nextWorkDate(date, '+', 9), days: 1};
        // 10 days after
        var floorCover = {site_id: site_id, entity_type: 't', entity_id: 9, task_id: 100, from: nextWorkDate(date, '+', 10), to: nextWorkDate(date, '+', 10), days: 1};
        // 11 days after
        var pointing = {site_id: site_id, entity_type: 't', entity_id: 9, task_id: 108, from: nextWorkDate(date, '+', 11), to: nextWorkDate(date, '+', 11), days: 1};
        // 12 days after
        var platformDnLab = {site_id: site_id, entity_type: 't', entity_id: 21, task_id: 221, from: nextWorkDate(date, '+', 12), to: nextWorkDate(date, '+', 12), days: 1};
        var platformDnCarp = {site_id: site_id, entity_type: 't', entity_id: 2, task_id: 25, from: nextWorkDate(date, '+', 12), to: nextWorkDate(date, '+', 12), days: 1};
        var polEaves = {site_id: site_id, entity_type: 't', entity_id: 2, task_id: 10, from: nextWorkDate(date, '+', 12), to: nextWorkDate(date, '+', 13), days: 2};
        var catwalkUp = {site_id: site_id, entity_type: 't', entity_id: 2, task_id: 27, from: nextWorkDate(date, '+', 12), to: nextWorkDate(date, '+', 12), days: 1};
        // 13 days after
        var frameInspect = {site_id: site_id, entity_type: 'c', entity_id: 23, task_id: 184, from: nextWorkDate(date, '+', 13), to: nextWorkDate(date, '+', 13), days: 1};
        // 14 days after
        var genClean = {site_id: site_id, entity_type: 't', entity_id: 21, task_id: 198, from: nextWorkDate(date, '+', 14), to: nextWorkDate(date, '+', 14), days: 1};


        // Create Promise array then excute
        var promises = [];

        var promise = addTaskToPlanner(plan, preConst);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, startJob);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, loadJob);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, errectScaff);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, roofMaint);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, startCarp);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, layFloor);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, electDriveby);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, plumbDriveby);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, floorInspect);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, frameRoof);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, loadPlatform);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, platformUpLab);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, platformUpCarp);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, fasciaGutter);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, floorCover);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, pointing);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, platformDnLab);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, platformDnCarp);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, polEaves);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, catwalkUp);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, frameInspect);
        promises.push(promise);
        var promise = addTaskToPlanner(plan, genClean);

        Promise.all(promises).then(resolve);

        // Email Jobstart
        var data = {site_id: site_id, newdate: date};
        $.ajax({
            url: '/planner/data/trade/email-jobstart',
            type: 'POST',
            data: data,
            success: function (result) {
                console.log('Emailed Jobstart');
            },
            error: function (result) {
                alert("Failed to email Job Start");
                console.log('Email of Job Start FAILED');
            }
        });
    });
}

// Add Start task to planner. Only if added to DB successfully
function addTaskToPlanner(plan, task) {
    addTaskDB(task).then(function (result) {
        if (result) {
            task.id = result.id
            plan.push(task);
        }
    }.bind(this));
}

/*
 * Database Functions
 */

// Get task from Database and return a 'promise'
function getTaskDB(task_id) {
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: '/planner/'+task_id,
            type: 'GET',
            success: function (result) {
                console.log('DB got task:[' + result.id + '] ' + result.task_name + ' F:' + result.from + ' T:' + result.to + ' days:' + result.days);
                /*if (task.entity_type == 'c' && moment(moment().format('YYYY-MM-DD')).isBetween(task.from, task.to, null, '[]')) {
                 console.log('Added task on today so also add company to roster');
                 addCompanyOnRosterDB(task.site_id, moment().format('YYYY-MM-DD'), task.entity_id);
                 }*/
                resolve(result);
            },
            error: function (result) {
                alert("Failed getting task. Please refresh the page to resync planner");
                console.log('DB get task FAILED');
                reject(false);
            }
        });
    });
}

// Add task to Database and return a 'promise'
function addTaskDB(task) {
    return new Promise(function (resolve, reject) {
        delete task._method; // ensure _method not set else throws a Laravel MethodNotAllowedHttpException error. Requires a POST request to @store
        $.ajax({
            url: '/planner',
            type: 'POST',
            data: task,
            success: function (result) {
                console.log('DB added task:[' + result.id + '] ' + task.task_name + ' F:' + task.from + ' T:' + task.to + ' days:' + task.days);
                /*if (task.entity_type == 'c' && moment(moment().format('YYYY-MM-DD')).isBetween(task.from, task.to, null, '[]')) {
                 console.log('Added task on today so also add company to roster');
                 addCompanyOnRosterDB(task.site_id, moment().format('YYYY-MM-DD'), task.entity_id);
                 }*/
                resolve(result);
            },
            error: function (result) {
                alert("Failed adding new task " + task.task_name + '. Please refresh the page to resync planner');
                console.log('DB added task FAILED:[' + result.id + '] ' + task.task_name + ' F:' + task.from + ' T:' + task.to + ' days:' + task.days);
                reject(false);
            }
        });
    });
}

// Update task in Database and return a 'promise'
function updateTaskDB(task) {
    return new Promise(function (resolve, reject) {
        task._method = 'patch';
        $.ajax({
            url: '/planner/' + task.id,
            type: 'POST',
            data: task,
            success: function (result) {
                delete task._method;
                console.log('DB updated task:' + task.id + ' F:' + task.from + ' T:' + task.to + ' Days:' + task.days + ' EID:' + task.entity_id);
                resolve(task);
            },
            error: function (result) {
                alert("failed updating task " + task.task_name + '(' + task.id + ')' + '. Please refresh the page to resync planner');
                console.log('DB updated task FAILED:' + task.id + ' F:' + task.from + ' T:' + task.to + ' Days:' + task.days + ' EID:' + task.entity_id);
                reject(false);
            }
        });
    });
}

// Delete task from Database and return a 'promise'
function deleteTaskDB(task) {
    //console.log('Deleting task:' + task.id + ' F:' + task.from + ' T:' + task.to + ' Days:' + task.days + ' EID:' + task.entity_id);
    return new Promise(function (resolve, reject) {
        task._method = 'delete';
        $.ajax({
            url: '/planner/' + task.id,
            type: 'POST',
            data: task,
            success: function (result) {
                delete task._method;
                console.log('DB deleted task:' + task.id + ' F:' + task.from + ' T:' + task.to + ' Days:' + task.days + ' EID:' + task.entity_id);
                /*if (task.entity_type == 'c' && moment(moment().format('YYYY-MM-DD')).isBetween(task.from, task.to, null, '[]')) {
                 console.log('Deleted task on today so also delete company from roster');
                 deleteCompanyOnRosterDB(task.site_id, moment().format('YYYY-MM-DD'), task.entity_id);
                 }*/
                resolve(task);
            },
            error: function (result) {
                alert("failed deleting task " + task.task_name + '. Please refresh the page to resync planner');
                console.log('DB deleted task FAILED:' + task.id + ' F:' + task.from + ' T:' + task.to + ' Days:' + task.days + ' EID:' + task.entity_id);
                reject(false);
            }
        });
    });
}

// Delete Company from Roster on given site & date and return a 'promise'
function deleteCompanyOnRosterDB(site_id, date, company_id) {
    //console.log('Deleting company:' + company_id + ' from Roster site:' + site_id + ' Date:' + date);
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: '/planner/data/roster/del-company/' + company_id + '/site/' + site_id + '/date/' + date,
            type: 'GET',
            success: function (result) {
                console.log('DB Deleted company:' + company_id + ' from Roster site:' + site_id + ' Date:' + date);
                resolve(true);
            },
            error: function (result) {
                alert("failed deleting company from roster" + '. Please refresh the page to resync planner');
                console.log('DB Deleted company FAILED:' + company_id + ' from Roster site:' + site_id + ' Date:' + date);
                reject(false);
            }
        });
    });
}

// Add Company to Roster on given site & date and return a 'promise'
function addCompanyOnRosterDB(site_id, date, company_id) {
    //console.log('Adding company:' + company_id + ' to Roster site:' + site_id + ' Date:' + date);
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: '/planner/data/roster/add-company/' + company_id + '/site/' + site_id + '/date/' + date,
            type: 'GET',
            success: function (result) {
                console.log('DB Added company:' + company_id + ' to Roster site:' + site_id + ' Date:' + date);
                resolve(true);
            },
            error: function (result) {
                alert("failed adding company to roster" + '. Please refresh the page to resync planner');
                console.log('DB Added company FAILED:' + company_id + ' to Roster site:' + site_id + ' Date:' + date);
                reject(false);
            }
        });
    });
}

// Allocate Site to a Supervisor and return a 'promise'
function allocateSiteToSupervisor(site_id, user_id) {
    //console.log(''Allocating site:' + site_id + ' super :' + user_id);
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: '/planner/data/site/' + site_id + '/allocate/' + user_id,
            type: 'GET',
            success: function (result) {
                console.log('DB Allocated site:' + site_id + ' super :' + user_id);
                resolve(true);
            },
            error: function (result) {
                alert("failed allocating site to supervisor" + '. Please refresh the page to resync planner');
                console.log('DB Allocated site FAILED:' + site_id + ' super :' + user_id);
                reject(false);
            }
        });
    });
}

// Import Given Site into current Plan
function importSite(plan, site_id) {
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: '/planner/data/site/' + site_id,
            type: 'GET',
            success: function (result) {
                //console.log(result[1]);
                var import_plan = result[1];
                for (var i = 0; i < import_plan.length; i++) {
                    var task = import_plan[i];
                    if (!objectFindByKey(plan, 'id', task.id)) {
                        plan.push(task);
                        console.log('added id:' + task.id + ' ' + task.task_name + ' by:' + task.entity_name);
                    } else
                        console.log('id:' + task.id + ' existed on plan');
                }
                resolve(true);
            },
            error: function (result) {
                alert("failed deleting task " + task.task_name + '. Please refresh the page to resync planner');
                reject(false);
            }
        });
    });
}
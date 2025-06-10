var xx = {
    dev: dev, permission: '',
    params: {date: '', supervisor_id: '', site_id: '', site_start: 'week', trade_id: '', _token: $('meta[name=token]').attr('value')},
    status: '', first_date: '', start_date: '', start_carp: '', final_date: '', carp_prac: '', holidays: '',
    council_approval: '', contract_sent: '', contract_signed: '', deposit_paid: '',
    first_mon: '', start_mon: '', final_mon: '', this_mon: moment().day(1).format('YYYY-MM-DD'), today: moment().format('YYYY-MM-DD'),
    total_weeks: '', first_week: 1, current_week: 1,
    showSidebar: false, showSidebarHeader: false, showNewTask: false, showAssign: false, showClearModal: false,
    enableActions: false, load_plan: false,
    day_date: '', day_etype: '', day_eid: '', day_eid2: '', day_ename: '',
    day_task_id: '', day_task_code: '', day_task_name: '', day_move_days: 1,
    assign_trade: '', assign_type: '', assign_cid: '', assign_cname: '', assign_tasks: '',
    day_conflicts: '', day_other_sites: '',
    day_plan: [], connected_tasks: [],
    sel_trade: [], sel_company: [], sel_task: [],
    maxjobs: [], leave: [],
    plan: [],
    trades: [], tasks: [],
};

Vue.component('app-siteplan', {
    template: '#siteplan-template',
    props: ['site_id'],
    created: function () {
        this.getPlan();
    },
    data: function () {
        return {xx: xx};
    },
    components: {
        sidebar: VueStrap.aside,
        sidebarheader: VueStrap.aside,
        modal: VueStrap.modal,
    },
    filters: {
        formatDate: function (date) {
            return moment(date).format('DD/MM/YYYY');
        },
        formatDate2: function (date) {
            return moment(date).format('ddd DD/MM');
        },
        formatDate3: function (date) {
            return moment(date).format('DD/MM');
        },
    },
    methods: {
        openSidebarHeader: function (date) {
            // Open Header sidebar and initialise data
            this.xx.showSidebarHeader = true;
            this.xx.showNewTask = false;
            this.xx.day_date = date;
            //this.xx.day_plan = [];
            this.xx.sel_task = [];
            this.xx.day_eid = '';
            this.xx.day_etype = '';
            this.xx.day_ename = '';
            this.xx.day_move_days = 1;
            this.xx.assign_trade = '';
            this.getDayPlan(this.xx.day_date);
            this.getTradeOptions();
        },
        gotoURL: function (url) {
            postAndRedirect(url, this.xx.params);
        },
        weekDateHeader: function (date, days) {
            var currentYear = moment().year();
            var cellYear = moment(date).add(days, 'days').year();
            if (currentYear == cellYear)
                return moment(date).add(days, 'days').format('DD/MM');
            else
                return moment(date).add(days, 'days').format('DD/MM/YY');
        },
        weekDate: function (date, days) {
            return moment(date).add(days, 'days').format('YYYY-MM-DD');
        },
        calcWeekNumber: function (x) {
            // calculate given week number for planner determined by 'start' date
            if (this.xx.first_week === 1) return x + 1;
            var y = x + this.xx.first_week + 1;
            if (y < 1) y--;
            return y;
        },
        showWeek: function (date) {
            // determine if given date is before this monday - used for displaying First Task / Start of Job / This Week
            //return !(this.xx.params.site_start === 'week' && moment(date).isBefore(moment(this.xx.this_mon), 'day'));
            if (this.xx.params.site_start === 'week' && moment(date).isBefore(moment(this.xx.this_mon), 'day'))
                return false;
            if (this.xx.params.site_start === 'start' && moment(date).isBefore(moment(this.xx.start_mon), 'day'))
                return false;

            return true;
        },
        showAdminDate: function (date) {
            // determine if current week has site admin event
            var admin_event = false;
            var admin_mon = this.adminDate(moment(date).format('YYYY-MM-DD'));
            var admin_tue = this.adminDate(moment(date).add(1, 'days').format('YYYY-MM-DD'));
            var admin_wed = this.adminDate(moment(date).add(2, 'days').format('YYYY-MM-DD'));
            var admin_thu = this.adminDate(moment(date).add(3, 'days').format('YYYY-MM-DD'));
            var admin_fri = this.adminDate(moment(date).add(4, 'days').format('YYYY-MM-DD'));
            if (admin_mon || admin_tue || admin_wed || admin_thu || admin_fri)
                admin_event = true;

            //console.log(date + '**' + admin_mon + ':' + admin_tue + ':' + admin_wed + ':' + admin_thu + ':' + admin_fri);

            // determine if given date is before this monday - used for displaying First Task / Start of Job / This Week
            if (this.xx.params.site_start === 'week' && moment(date).isBefore(moment(this.xx.this_mon), 'day'))
                return false;
            if (this.xx.params.site_start === 'start' && moment(date).isBefore(moment(this.xx.start_mon), 'day'))
                return false;

            return admin_event;
        },
        pastDate: function (date) {
            // determine if given date is same or before today
            return (moment(date).isSameOrBefore(moment(), 'day') || this.xx.permission == 'view');
        },
        publicHoliday: function (date) {
            // determine if given date is public holiday
            if (date in this.xx.holidays)
                return this.xx.holidays[date];
            return '';
        },
        todayDate: function (date) {
            // determine if given date is today
            return moment(date).isSame(moment(), 'day');
        },
        todayTask: function (task) {
            return true;
            //return moment(xx.day_date).isBetween(task.from, task.to, null, '[]');
        },
        showNewTask: function () {
            this.xx.showNewTask = true;
            // Hack - set day_eid fror eid2 because eid not set on initial load of sidebar
            this.xx.day_eid = this.xx.day_eid2;
        },
        getPlan: function () {
            // Get plan from database and initialise planner variables
            this.xx.params.site_id = this.site_id;
            this.xx.plan = [];

            if (this.xx.params.site_id) {
                this.xx.load_plan = true;
                //console.log('/planner/site/' + this.xx.params.site_id + '/' + this.xx.params.site_start + '/plan');
                $.getJSON('/planner/data/site/' + this.xx.params.site_id, function (plan) {
                    this.xx.plan = plan[1];
                    this.xx.maxjobs = plan[2];
                    this.xx.leave = plan[3];
                    this.xx.permission = plan[4];
                    this.xx.holidays = plan[5];
                    if (plan[1].length > 0) {
                        // Determine + set key dates on planner ie. first, last, start etc
                        this.xx.first_date = plan[0]['first_date'];
                        this.xx.first_mon = moment(this.xx.first_date).day(1).format('YYYY-MM-DD');
                        this.xx.final_date = plan[0]['final_date'];
                        this.xx.start_date = plan[0]['start_date'];
                        this.xx.start_mon = moment(this.xx.start_date).day(1).format('YYYY-MM-DD');
                        this.xx.start_carp = plan[0]['start_carp'];
                        this.xx.carp_prac = plan[0]['carp_prac'];
                        if (this.xx.start_date) {
                            this.xx.first_week = moment(this.xx.start_date).day(1).diff(moment(this.xx.first_date).day(1), 'weeks');
                            if (this.xx.first_week === 0) this.xx.first_week++;
                            else this.xx.first_week = this.xx.first_week * -1;
                            //this.xx.current_week = this.xx.first_week;
                        }
                        this.xx.final_mon = moment(this.xx.final_date).day(1).format('YYYY-MM-DD');

                        // Calc number of weeks on Planner - if today > final_date plan to today + 10 weeks
                        var a = moment(this.xx.first_mon).day(1);
                        var b = moment(this.xx.final_mon).day(1);
                        if (moment(this.xx.this_mon).isAfter(b))
                            b = moment(this.xx.this_mon);
                        this.xx.total_weeks = b.diff(a, 'weeks') + 11;
                    } else {
                        this.xx.first_mon = this.xx.this_mon;
                        this.xx.total_weeks = 20;
                    }
                    this.xx.load_plan = false;
                }.bind(this));
                this.$broadcast('refreshWeekPlanEvent');
            }
        },
        getDayPlan: function (date) {
            // Get all tasks for given date
            this.xx.day_plan = tasksOnDate(this.xx.plan, date);
        },
        getTradeOptions: function () {
            // Get Company + Trade options for use with select dropdowns
            this.xx.sel_trade = [];
            this.xx.sel_company = [];

            // Get generic trade list
            $.getJSON('/planner/data/trade', function (trades) {
                this.xx.sel_trade = trades;
            }.bind(this));
            // Get Company list
            $.getJSON('/planner/data/company/all/trade/all/site/' + this.xx.params.site_id, function (companies) {
                this.xx.sel_company = companies;
            }.bind(this));
        },
        updateCompanyOptions: function () {
            this.xx.day_etype = '';
            // Get possible companies that are skilled in given trade
            $.getJSON('/planner/data/company/match-trade/trade/' + this.xx.assign_trade + '/site/' + this.xx.params.site_id, function (companies) {
                this.xx.sel_company = companies;
            }.bind(this));
        },
        updateTaskOptions: function () {
            // Get tasks options for given Trade or Company
            if (this.xx.day_eid == 'gen') {
                this.xx.day_etype = 't';
                this.xx.day_eid = this.xx.assign_trade;
                $.getJSON('/planner/data/trade/' + this.xx.day_eid + '/tasks', function (tasks) {
                    this.xx.sel_task = tasks;
                    // Find name of trade from day array
                    var result = objectFindByKey(this.xx.sel_trade, 'value', this.xx.day_eid);
                    this.xx.day_ename = result.text;
                }.bind(this));
            } else { //if (this.xx.day_etype == 'c') {
                this.xx.day_etype = 'c';
                $.getJSON('/planner/data/company/' + this.xx.day_eid + '/tasks/trade/' + this.xx.assign_trade, function (tasks) {
                    this.xx.sel_task = tasks;
                    // Find name of company from day array
                    var result = objectFindByKey(this.xx.sel_company, 'value', this.xx.day_eid);
                    this.xx.day_ename = result.name;
                }.bind(this));
            }
        },
        assignTradeOptions: function () {
            // Get possible trades that the given entity can do.
            this.xx.showAssign = true;
            this.xx.day_eid = this.xx.day_eid2; // Hack assign eid from eid2

            if (this.xx.day_etype == 't') {
                // Entity is a generic trade so can easy determine companies that are skilled in given trade
                this.xx.assign_trade = this.xx.day_eid;
                this.assignCompanyOptions();
            } else if (this.xx.day_etype == 'c') {
                // Enitity is a company so many be skilled in multiple trades so get list of possible trades
                $.getJSON('/planner/data/company/' + this.xx.day_eid + '/trades', function (trades) {
                    this.xx.sel_trade = trades;
                    // Set assign_trade if company only skilled 1 trade
                    if (trades.length === 2) {
                        this.xx.assign_trade = trades[1].value;
                        this.assignCompanyOptions();
                    }
                }.bind(this));
            }
        },
        assignCompanyOptions: function () {
            // Get possible companies that are skilled in given trade
            $.getJSON('/planner/data/company/match-trade/trade/' + this.xx.assign_trade + '/site/' + this.xx.params.site_id, function (companies) {
                this.xx.sel_company = companies;
            }.bind(this));
        },
        assignCompanyName: function () {
            if (this.xx.assign_cid == 'gen') {
                this.xx.assign_type = 't';
                // Search Day Trade List to get extra data fields company name
                for (var i = 0; i < this.xx.sel_trade.length; i++) {
                    if (this.xx.sel_trade[i]['value'] == this.xx.assign_trade)
                        this.xx.assign_cname = this.xx.sel_trade[i]['name'];
                }
            } else {
                this.xx.assign_type = 'c';
                // Search Day Company List to get extra data fields company name
                for (var i = 0; i < this.xx.sel_company.length; i++) {
                    if (this.xx.sel_company[i]['value'] == this.xx.assign_cid)
                        this.xx.assign_cname = this.xx.sel_company[i]['name'];
                }
            }

        },
        assignTasks: function () {
            // Assign specified tasks to given company or generic
            this.xx.showSidebar = false;
            if (this.xx.assign_type == 't') {
                this.xx.assign_cid = this.xx.assign_trade;
            }
            assignTasksFromDate(this.xx.plan, this.xx.params.site_id, this.xx.assign_type, this.xx.assign_cid, this.xx.assign_cname, this.xx.assign_tasks, this.xx.assign_trade, this.xx.day_date)
                .then(function (result) {
                    if (result) {
                        this.xx.day_etype = '';
                        this.xx.day_ename = '';
                        this.$broadcast('refreshWeekPlanEvent');
                        console.log('refreshed planner');
                    }
                }.bind(this));

            setTimeout(function () {
                this.$broadcast('refreshWeekPlanEvent');
                console.log('delayed refreshed planner');
            }.bind(this), 3000);
        },
        addTask: function () {
            // Add task to planner
            this.xx.showNewTask = false;

            // Create new task
            var newtask = {
                id: '', site_id: this.xx.params.site_id, entity_type: this.xx.day_etype, entity_id: this.xx.day_eid,
                entity_name: this.xx.day_ename, task_id: this.xx.day_task_id,
                task_code: '', task_name: 'Task Unassigned', trade_id: '', trade_name: '',
                from: this.xx.day_date, to: this.xx.day_date, days: 1
            }

            // Search Day Task List to get extra data fields task_name + task_code
            for (var i = 0; i < this.xx.sel_task.length; i++) {
                if (this.xx.sel_task[i]['value'] == this.xx.day_task_id) {
                    newtask.task_name = this.xx.sel_task[i]['name'];
                    newtask.task_code = this.xx.sel_task[i]['code'];
                    newtask.trade_id = this.xx.sel_task[i]['trade_id'];
                    newtask.trade_name = this.xx.sel_task[i]['trade_name'];
                }
            }

            // Determine if task is valid and throw error if not.
            // Check for special cases ie 'START' + 'STARTCarp' and prevent duplicate task
            var validTask = true;

            // Don't allow any task to be added prior to 'START' task (except Pre-construction meeting)
            // - since adding Pre-construction planner we'll allow task to be added before START task
            /*
             if (this.xx.start_date && moment(this.xx.day_date).isBefore(this.xx.start_date)) {
             validTask = false;
             toastr.error('Unable to add tasks before "Start Job"');
             } else if (newtask.task_code === 'START') {
             toastr.error("This task can only be added by the Trade Planner Actions button");
             validTask = false;
             } else if (this.xx.start_date == '' && this.xx.status != 2) {
             toastr.error("You can't add a task to the planner until it has a START Job");
             validTask = false;
             }*/
            if (newtask.task_code === 'START') {
                if (this.xx.status == 1) {
                    toastr.error("This task can only be added by the Trade Planner Actions button");
                    validTask = false;
                } else if (this.xx.start_date === '') { // Check for 'START' tasks
                    this.xx.start_date = moment(this.xx.day_date).format('YYYY-MM-DD');
                    //validTask = false; // Task is vaild but we set to false to prevent it being added twice

                    // Hide Sidebars
                    this.xx.showSidebar = false;
                    this.xx.showSidebarHeader = false;

                    // Delay adding of job just a sec to enable us to display loading spinner first
                    this.xx.load_plan = true;
                    setTimeout(function () {
                        //alert('date:'+date+' '+direction+days+' days');
                        addStartTaskToPlanner2(this.xx.plan, this.xx.params.site_id, this.xx.day_date)
                            .then(function (result) {
                                if (result) {
                                    //this.xx.load_plan = false;
                                    //this.$broadcast('refreshWeekPlanEvent');
                                }
                            }.bind(this));
                    }.bind(this), 100);

                    // Lock Planner + Show loading Spinner to allow for all the tasksto be added
                    setTimeout(function () {
                        //this.getPlan();
                        this.xx.load_plan = false;
                        console.log('delayed reloading of page for just a moment')
                        this.$broadcast('refreshWeekPlanEvent');
                        postAndRedirect('/planner/preconstruction', this.xx.params);
                    }.bind(this), 5000);

                } else {
                    toastr.error("'" + newtask.task_name + "' already exists on planner " + moment(this.xx.start_date).format('DD/MM/YYYY'));
                    validTask = false;
                }
            }
            if (newtask.task_code === 'STARTCarp') {   // Check for 'STARTCarp' tasks
                if (this.xx.start_carp === '') {
                    this.xx.start_carp = moment(this.xx.day_date).format('YYYY-MM-DD');
                } else {
                    toastr.error("'" + newtask.task_name + "' already exists on planner " + moment(this.xx.start_carp).format('DD/MM/YYYY'));
                    validTask = false;
                }
            }
            if (newtask.task_id === '5') {   // Check for 'Prac complete' tasks
                if (this.xx.carp_prac === '') {
                    this.xx.carp_prac = moment(this.xx.day_date).format('YYYY-MM-DD');
                } else {
                    toastr.error("'" + newtask.task_name + "' already exists on planner " + moment(this.xx.carp_prac).format('DD/MM/YYYY'));
                    validTask = false;
                }
            }

            if (validTask && newtask.task_code != 'START') {
                // Add new task to DB 'then' if successful add to planner
                addTaskDB(newtask).then(function (result) {
                    if (result) {
                        newtask.id = result.id;
                        this.xx.plan.push(newtask);
                        this.xx.day_plan.unshift(newtask);
                        this.xx.day_task_id = '';
                        this.$broadcast('refreshWeekPlanEvent');
                        this.xx.connected_tasks = connectedTasks(this.xx.plan, this.xx.params.site_id, this.xx.day_etype, this.xx.day_eid, this.xx.day_date);
                    }
                }.bind(this));
            }

            // Reset entity type + seltasks options for 'Header' Sidebar
            if (this.xx.showSidebarHeader) {
                this.xx.day_etype = '';
                this.xx.sel_task = [];
            }
        },
        deleteTask: function (task) {
            // Delete task from DB 'then' if successful delete from planner

            // If task begins before today then delete only from after today ie update the 'to' date + 'days'
            if (moment(this.xx.today).isBetween(task.from, task.to, null, '[]')) {
                // Calc date to end task on  ie. if today is a Sat or Sun then set to fri otherwise set to today
                var today = moment(this.xx.today);
                if (today.day() === 6 || today.day() === 0)
                    task.to = nextWorkDate(this.xx.today, '-', 1);
                else
                    task.to = this.xx.today;

                task.days = workDaysBetween(task.from, task.to);
                console.log('deleted part task id:[' + task.id + '] ' + task.task_name + ' F:' + task.from + ' T:' + task.to + ' days:' + task.days);
                updateTaskDB(task);
                toastr.warning('Only deleted task after today');
                this.xx.connected_tasks = connectedTasks(this.xx.plan, this.xx.params.site_id, this.xx.day_etype, this.xx.day_eid, this.xx.day_date);
                this.$broadcast('refreshWeekPlanEvent');
            } else
                this.deleteTaskDB(task);
        },
        deleteTaskDB: function (task) {
            deleteTaskDB(task)
                .then(function (result) {
                    // Check for special cases ie 'START' + 'STARTCarp' + Carp Prac'
                    if (task.task_code === 'START') this.xx.start_date = '';
                    if (task.task_code === 'STARTCarp') this.xx.start_carp = '';
                    if (task.task_id == '5') this.xx.carp_prac = '';

                    // Remove task from planners
                    console.log('Removing task from planners  task:' + task.id + ' F:' + task.from + ' T:' + task.to + ' Days:' + task.days + ' EID:' + task.entity_id);
                    this.xx.day_plan.$remove(task);
                    this.xx.plan.$remove(task);
                    this.xx.connected_tasks = connectedTasks(this.xx.plan, this.xx.params.site_id, this.xx.day_etype, this.xx.day_eid, this.xx.day_date);
                    this.$broadcast('refreshWeekPlanEvent');

                    // Hide sidebar if deleted last task for date
                    if (this.xx.day_plan.length < 1) this.xx.showSidebar = false;
                }.bind(this));
        },
        addTaskDays: function (task) {
            // Increase number of days for given task

            // Prevent special tasks 'START' + 'STARTCarp' being more then 1 day
            if (task.task_code === 'START' || task.task_code === 'STARTCarp') {
                toastr.error("'" + task.task_name + "' can't exceed 1 day");
            } else {
                var days = task.days + 1;
                this.updateTaskToDate(task, days);
            }
        },
        subTaskDays: function (task) {
            // Decrease number of days for given task - prevent < 1
            if (task.days != 1) {
                var days = task.days - 1;
                this.updateTaskToDate(task, days);
            }
        },
        updateTaskToDate: function (task, days) {
            // Update given task 'to' date detemined by 'from' date + number of task 'days'

            // Disable Sidebar actions just a sec to ensure user doesn't spam buttons
            this.xx.enableActions = false;
            setTimeout(function () {
                this.xx.enableActions = true;
            }.bind(this), 500);

            var copyTask = jQuery.extend({}, task) // copy task
            copyTask.days = days;
            updateTaskToDate(copyTask).then(function (result) {
                if (result) {
                    task.days = copyTask.days;
                    task.to = copyTask.to;
                    task.from = copyTask.from;
                    this.$broadcast('refreshWeekPlanEvent');
                }
            }.bind(this));
        },
        moveTaskFromDate: function (task, direction, days) {
            // Move given task 'x' days forward '+' or backwards '-'

            if (task.task_code === 'START') {
                // Disable Sidebar actions just a sec to ensure user doesn't spam buttons
                setTimeout(function () {
                    this.xx.enableActions = true;
                }.bind(this), 3000);

                // if 'START' task then move whole job + attempt to also move tasks before it.
                // ie. move any tasks that are on planner after today along with START as long as they don't move to today
                var dayBefore = nextWorkDate(task.from, '-', '1');
                var dayAfterToday1 = nextWorkDate(this.xx.today, '+', '1');
                var dayAfterToday2 = nextWorkDate(this.xx.today, '+', '2');
                if (direction === '-' && moment(dayBefore).isSameOrBefore(moment(), 'day'))
                    toastr.error('Unable to move tasks to or before today!!');
                else if (direction === '-')
                    this.moveJobFromDate(dayAfterToday2, '-', 1);
                else
                    this.moveJobFromDate(dayAfterToday1, '+', 1);

                this.xx.enableActions = false;
            } else {
                // Disable Sidebar actions just a sec to ensure user doesn't spam buttons
                this.xx.enableActions = false;
                setTimeout(function () {
                    this.xx.enableActions = true;
                }.bind(this), 500);

                //var copyTask = jQuery.extend({}, task) // copy task
                moveTaskFromDate(this.xx.plan, task, this.xx.day_date, direction, days).then(function (result) {
                    if (result) {
                        if (task.id != result.id) {
                            this.xx.showSidebar = false;
                            this.xx.showSidebarHeader = false;
                        } else
                            task = result;

                        console.log('moved task[' + task.id + '] to new date');
                        //this.xx.connected_tasks = connectedTasks(this.xx.plan, this.xx.params.site_id, task.entity_type, task.entity_id, this.xx.day_date);
                        this.$broadcast('refreshWeekPlanEvent');
                    }
                }.bind(this), function (err) {
                    console.log('unable to move task to or before today');
                });
            }
        },
        moveJobFromDate: function (date, direction, days) {
            // Prevent job being moved to before today
            if (direction === '-' && moment(nextWorkDate(date, direction, days)).isSameOrBefore(moment(), 'day')) {
                toastr.error('Unable to move tasks to or before today!');
            } else {
                this.xx.showSidebarHeader = false;
                // Delay moving of job just a sec to enable us to display loading spinner first
                this.xx.load_plan = true;
                setTimeout(function () {
                    //alert('date:'+date+' '+direction+days+' days');
                    moveJobFromDate(this.xx.plan, this.xx.params.site_id, date, direction, days)
                        .then(function (result) {
                            if (result) {
                                setTimeout(function () {
                                    //this.xx.load_plan = false;
                                    //this.$broadcast('refreshWeekPlanEvent');
                                    //console.log('delayed refreshed planner');
                                    console.log('reload planner');
                                    if (this.xx.status == '-1')
                                        var url = "/planner/preconstruction";
                                    else
                                        var url = "/planner/site";
                                    postAndRedirect(url, this.xx.params);
                                    //this.getPlan();
                                }.bind(this), 3000);
                            }
                        }.bind(this));
                }.bind(this), 100);
            }
        },
        moveEntityFromDate: function (date, direction, days) {
            // Prevent entity being moved to or before today
            if (direction === '-' && moment(nextWorkDate(date, direction, days)).isSameOrBefore(moment(), 'day')) {
                toastr.error('Unable to move tasks to or before today');
            } else {
                this.xx.showSidebar = false;
                // Delay moving of entity just a sec to enable us to display loading spinner first
                this.xx.load_plan = true;
                setTimeout(function () {
                    //alert('date:'+date+' '+direction+days+' days');
                    moveEntityFromDate(this.xx.plan, this.xx.connected_tasks, date, direction, days)
                        .then(function (result) {
                            if (result) {
                                this.xx.load_plan = false;
                                this.$broadcast('refreshWeekPlanEvent');
                            }
                        }.bind(this));
                }.bind(this), 100);
            }
        },
        clearSiteFromDate: function () {
            // Delete all task from a given date
            var temp_plan = this.xx.plan.slice();  // Make copy of original plans

            // Search Plan and delete all Tasks from specified date
            for (var i = 0; i < this.xx.plan.length; i++) {
                var from = moment(this.xx.plan[i]['from']).format('YYYY-MM-DD');
                if (moment(from).isSameOrAfter(this.xx.day_date, 'day')) {
                    var task = this.xx.plan[i];
                    deleteTaskDB(task)
                        .then(function (result) {
                            // Check for special cases ie 'START' + 'STARTCarp'
                            if (result.task_code === 'START') this.xx.start_date = '';
                            if (result.task_code === 'STARTCarp') this.xx.start_carp = '';

                            // Remove task from planners
                            console.log('Removing task from planner  task:' + result.id + ' F:' + result.from + ' T:' + result.to + ' Days:' + result.days + ' EID:' + result.entity_id);
                            temp_plan.$remove(result);
                            this.$broadcast('refreshWeekPlanEvent');
                        }.bind(this));
                }
            }
            // Refresh original plans from updated 'copy' after delete
            this.xx.plan = temp_plan;
            this.$broadcast('refreshWeekPlanEvent');
            this.xx.showClearModal = false;
            this.xx.showSidebarHeader = false;
            toastr.success('Cleared Site from ' + moment(this.xx.day_date).format('DD/MM/YYYY'));
            console.log('reload planner');
            if (this.xx.status == '-1')
                var url = "/planner/preconstruction";
            else
                var url = "/planner/site";
            postAndRedirect(url, this.xx.params);
        },
        deleteConnectedTasks: function () {
            // Delete all connected tasks from a given date
            for (var i = 0; i < this.xx.connected_tasks.length; i++) {
                var task = this.xx.connected_tasks[i];

                // If task begins before given date then delete only from after today ie. update the 'to' + 'days'
                if (moment(xx.day_date).isBetween(task.from, task.to, null, '(]')) {
                    task.to = nextWorkDate(xx.day_date, '-', 1);
                    task.days = workDaysBetween(task.from, task.to);
                    console.log('deleted part task id:[' + task.id + '] ' + task.task_name + ' F:' + task.from + ' T:' + task.to + ' days:' + task.days);
                    updateTaskDB(task);
                    toastr.warning('Only deleted task after today');
                    this.$broadcast('refreshWeekPlanEvent');
                } else {
                    this.deleteTaskDB(task);
                }
            }
            this.$broadcast('refreshWeekPlanEvent');
            this.xx.showSidebar = false;
            toastr.success('Deleted Connected Tasks');
        },
        adminDate: function (date) {
            var string = ''; //<div class='label label-sm label-success font-white' style='padding: 5px; margin:5px'>" + date +"</div><br>";
            if (date == this.xx.council_approval) {   // Council Approval
                string = string + "<div class='label label-sm label-success font-white' style='padding: 5px; margin:5px'>Council Approval</div><br>";
            }
            if (date == this.xx.contract_sent) {   // Contract Sent
                string = string + "<div class='label label-sm label-success font-white' style='padding: 5px; margin:5px'>Contract Sent</div><br>";
            }
            if (date == this.xx.contract_signed) {   // Contract Sent
                string = string + "<div class='label label-sm label-success font-white' style='padding: 5px; margin:5px'>Contract Signed</div><br>";
            }

            return string;
        },
    },
});


Vue.component('app-weekof', {
    template: '#weekof-template',
    props: ['mon'],

    created: function () {
        this.getEntityList();
        this.getWeekdayDates();
    },
    data: function () {
        return {
            tue: '', wed: '', thu: '', fri: '',
            entities: [], xx: xx,
        };
    },
    events: {
        refreshWeekPlanEvent: function () {
            // Refresh planner for given week if current or future week  ie. don't refresh past weeks
            if (moment(moment()).isSameOrBefore(this.fri)) {
                //alert('refresh week:' + this.mon);
                this.getEntityList();
                this.$broadcast('refreshDayPlanEvent')
            }
        },
    },
    methods: {
        getEntityList: function () {
            this.xx.load_plan = true;

            // Delay loading of weekly plan just a sec to enable us to display loading spinner first
            setTimeout(function () {
                this.entities = [];
                this.xx.load_plan = true;

                // Return list of Companies / Entities on for current week
                var result = $.grep(this.xx.plan, function (e) {
                    var friday = moment(this.mon).day(5).format('YYYY-MM-DD');
                    // Return if task from or to is between mon-fri
                    // OR from is before mon + to after fri ie spans whole week but starts before current
                    return (moment(e.from).isSameOrAfter(this.mon) && moment(e.from).isSameOrBefore(friday) ||
                        moment(e.to).isSameOrAfter(this.mon) && moment(e.to).isSameOrBefore(friday) ||
                        moment(e.from).isBefore(this.mon) && moment(e.to).isAfter(friday));
                }.bind(this));

                // Add found 'unique' Companies / Entities to entities array
                if (result) {
                    var array = [];
                    result.forEach(function (item) {
                        // Verify if unique then add to array
                        if (array.indexOf(item.entity_type + '.' + item.entity_id) == -1)
                            array.push(item.entity_type + '.' + item.entity_id);
                    });
                    this.entities = array.sort().reverse();
                }
                this.xx.load_plan = false;
            }.bind(this), 100);
        },
        getWeekdayDates: function () {
            // Set dates for all days of current week
            this.tue = moment(this.mon).add(1, 'days').format('YYYY-MM-DD');
            this.wed = moment(this.mon).add(2, 'days').format('YYYY-MM-DD');
            this.thu = moment(this.mon).add(3, 'days').format('YYYY-MM-DD');
            this.fri = moment(this.mon).add(4, 'days').format('YYYY-MM-DD');
        },
        adminDate: function (date) {
            var string = "<div class='label label-sm label-success font-white' style='padding: 5px; margin:5px'>" + date + "</div><br>"
            if (date == this.xx.council_approval) {   // Council Approval
                string = string + "<div class='label label-sm label-success font-white' style='padding: 5px; margin:5px'>Council Approval</div><br>";
            }
            if (date == this.xx.contract_sent) {   // Contract Sent
                string = string + "<div class='label label-sm label-success font-white' style='padding: 5px; margin:5px'>Contract Sent</div><br>";
            }
            if (date == this.xx.contract_signed) {   // Contract Sent
                string = string + "<div class='label label-sm label-success font-white' style='padding: 5px; margin:5px'>Contract Signed</div><br>";
            }

            return string;
        },
    },
});

Vue.component('app-dayplan', {
    props: ['date', 'entity'],
    template: '#dayplan-template',

    created: function () {
        this.getEntityPlan();
    },
    data: function () {
        return {
            etype: '', eid: '', ename: '',
            conflicts: '', onleave: 'empty',
            entity_plan: [],
            xx: xx,
        };
    },
    events: {
        refreshDayPlanEvent: function () {
            this.getEntityPlan();
        },
    },
    methods: {
        openSidebar: function (date) {
            // Get id + type for current Entity
            var arr = this.entity.split('.');
            this.etype = arr[0];
            this.eid = arr[1];

            // Open Entity sidebar and initialise data
            this.xx.showSidebar = true;
            this.xx.showNewTask = false;
            this.xx.showAssign = false;
            this.xx.enableActions = true;
            this.xx.day_plan = this.entity_plan;
            this.xx.day_date = date;
            this.xx.day_etype = this.etype;
            this.xx.day_eid = this.eid;
            this.xx.day_eid2 = this.eid;
            this.xx.day_ename = this.ename;
            this.xx.day_task_id = '';
            this.xx.day_conflicts = this.conflicts;
            this.xx.assign_cid = '';
            this.xx.assign_cname = '';
            this.xx.assign_trade = '';
            this.xx.assign_tasks = '';
            this.getTaskOptions();
            this.xx.connected_tasks = connectedTasks(this.xx.plan, this.xx.params.site_id, this.etype, this.eid, date);
        },
        getEntityPlan: function () {
            // Get plan for current Entity

            // Get id + type for current Entity
            var arr = this.entity.split('.');
            this.etype = arr[0];
            this.eid = arr[1];

            this.entity_plan = [];
            // Search Site Plan for Entity tasks on specific date
            for (var i = 0; i < this.xx.plan.length; i++) {
                if (this.xx.plan[i]['entity_id'] == this.eid && this.xx.plan[i]['entity_type'] == this.etype) {
                    this.ename = this.xx.plan[i]['entity_name']; // set entity name for xx.day_ename
                    var from = moment(this.xx.plan[i]['from']).format('YYYY-MM-DD');
                    var to = moment(this.xx.plan[i]['to']).format('YYYY-MM-DD');
                    // Allow tasks that span multiple days
                    if (moment(this.date).isBetween(from, to, null, '[]'))
                        this.entity_plan.push(this.xx.plan[i]);
                }
            }

            // Determine if Company has exceed 'maxjobs' for given date;
            if (this.etype === 'c' && this.eid != '' && this.xx.maxjobs[this.eid]) {
                if (this.xx.maxjobs[this.eid][this.date])
                    this.conflicts = this.xx.maxjobs[this.eid][this.date];
            }

            // Determine if Company is on leave
            if (this.etype === 'c' && this.eid != '' && this.xx.leave[this.eid]) {
                if (this.xx.leave[this.eid][this.date])
                    this.onleave = this.xx.leave[this.eid][this.date];
            }

            // Determine if Company has a 'string' of tasks
            // - a string defined as multiple tasks same day or tasks that are back to back from one another


        },
        getTaskOptions: function () {
            // Get tasks options for given Trade or Company

            // Hack - set day_eid2 because looses eid on sidebar load
            this.xx.day_eid2 = this.xx.day_eid;

            if (this.xx.day_etype == 't') {
                $.getJSON('/planner/data/trade/' + this.xx.day_eid + '/tasks', function (tasks) {
                    this.xx.sel_task = tasks;
                }.bind(this));
                this.xx.assign_trade = this.xx.day_eid;
                this.assignCompanyOptions();
            } else {
                $.getJSON('/planner/data/company/' + this.xx.day_eid + '/tasks/trade/all', function (tasks) {
                    this.xx.sel_task = tasks;
                }.bind(this));

                // Get other sites
                $.getJSON('/planner/data/company/' + this.xx.day_eid + '/site/' + this.xx.params.site_id + '/' + this.date, function (tasks) {
                    this.xx.day_other_sites = tasks;
                }.bind(this));

                // Get possible companies who they can asign tasks to.
                $.getJSON('/planner/data/company/' + this.xx.day_eid + '/trades', function (trades) {
                    this.xx.sel_trade = trades;
                    // Set assign_trade if company only skilled 1 trade
                    if (trades.length == 2) {
                        this.xx.assign_trade = trades[1].value;
                        this.assignCompanyOptions();
                    }
                }.bind(this));
            }
        },
        assignCompanyOptions: function () {
            // Get possible companies that are skilled in given trade
            $.getJSON('/planner/data/company/match-trade/trade/' + this.xx.assign_trade + '/site/' + this.xx.params.site_id, function (companies) {
                this.xx.sel_company = companies;
            }.bind(this));
        },
        pastDate: function (date) {
            // determine if given date is same or before today
            if (moment(date).isSameOrBefore(moment(), 'day') || this.xx.permission == 'view')
                return true;
            return false;
        },
        publicHoliday: function (date) {
            // determine if given date is public holiday
            if (date in this.xx.holidays)
                return this.xx.holidays[date];
            return '';
        },
        taskNameClass: function (task) {
            // Set class of task name for displaying on planner
            var str = '';

            if (task.task_code === 'START' || task.task_code === 'STARTCarp')
                str = str + ' label label-sm label-info font-white';
            else if (task.entity_type === 't')
                str = str + ' font-yellow-gold';

            return str;
        },
    },
});

var myApp = new Vue({
    el: 'body',
    data: {xx: xx},
    methods: {
        formatDate: function (date) {
            return moment(date).format('DD/MM/YYYY');
        },
        pastDate: function (date) {
            // determine if given date is same or before today
            if (moment(date).isSameOrBefore(moment(), 'day'))
                return true;
            return false;
        },
    },
});
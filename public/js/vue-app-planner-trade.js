var xx = {
    dev: dev, permission: '',
    params: {date: '', supervisor_id: '', site_id: '', site_start: '', trade_id: '', _token: $('meta[name=token]').attr('value')},
    mon_now: '', mon_this: '', mon_prev: '', mon_next: '', today: moment().format('YYYY-MM-DD'), jobstart: moment().format('DD/MM/YYYY'), week_selected: '',
    showSidebar: false, showSidebarUpcoming: false, showSidebarAddstart: false, showSidebarMovestart: false, showSidebarAllocate: false, showNewTask: false, showAssign: false, showClearModal: false,
    enableActions: false, load_plan: false, trade_name: '',
    day_date: '', day_etype: '', day_eid: '', day_eid2: '', day_ename: '', day_site_id: '',
    day_task_id: '', day_task_code: '', day_task_name: '', day_move_days: 1, day_upcoming: '',
    assign_site: '', assign_trade: '', assign_type: '', assign_cid: '', assign_cname: '', assign_tasks: '', assign_super: '',
    day_conflicts: '', day_other_sites: '',
    day_plan: [], day_sites: [], connected_tasks: [],
    sel_site: [], sel_trade: [], sel_company: [], sel_task: [], sel_jobstart: [], sel_joballocate: [], sel_super: [],
    sel_assign_tasks: [{value: '', text: 'Select Action'}, {value: 'all', text: 'All future tasks for this trade'}, {value: 'day', text: 'Only todays tasks for this trade'}],
    maxjobs: [], leave: [],
    plan: [], sites: [], companies: [], upcoming_task: [], upcoming_plan: [],
};


Vue.component('app-weekly', {
    props: ['mondate'],
    template: '#weekly-template',
    created: function () {
        this.getSites();
        //this.getPlan();
    },
    data: function () {
        return {xx: xx};
    },
    components: {
        sidebar: VueStrap.aside,
        sidebarupcoming: VueStrap.aside,
        sidebaraddstart: VueStrap.aside,
        sidebarmovestart: VueStrap.aside,
        sidebarallocate: VueStrap.aside,
        datepicker: VueStrap.datepicker,
        modal: VueStrap.modal,
    },
    filters: {
        formatDate: function (date) {
            return moment(date).format('DD/MM/YYYY');
        },
        formatDate2: function (date) {
            if (date) return (date.match(/^\d{4}-\d{2}-\d{2}$/)) ? moment(date).format('ddd DD/MM') : '';
        },
        formatDate3: function (date) {
            return moment(date).format('DD/MM');
        },
        max10chars: function (str) {
            return str.substring(0, 10);
        },
        max15chars: function (str) {
            return str.substring(0, 15);
        },
    },
    methods: {
        openSidebarUpcoming: function (task) {
            // Open Header sidebar and initialise data
            this.xx.showSidebarUpcoming = true;
            this.xx.enableActions = true;
            this.xx.day_upcoming = task;
            this.xx.day_date = task.from;
            this.xx.assign_site = task.site_id;
            this.xx.assign_trade = task.trade_id;
            this.xx.assign_tasks = '';
            this.xx.assign_cid = '';
            this.xx.sel_task = [];
            this.xx.day_plan = [task];
            this.assignCompanyOptions();
        },
        openSidebarAddstart: function () {
            // Open Jobstart sidebar and initialise data
            this.xx.showSidebarAddstart = true;
            $.getJSON('/planner/data/trade/jobstarts/false', function (sites) {
                this.xx.sel_jobstart = sites;
            }.bind(this));
        },
        openSidebarMovestart: function () {
            // Open Jobstart sidebar and initialise data
            this.xx.showSidebarMovestart = true;
            $.getJSON('/planner/data/trade/jobstarts/true', function (sites) {
                this.xx.sel_jobstart = sites;
            }.bind(this));
        },
        openSidebarAllocatejob: function () {
            // Open Allocate Job sidebar and initialise data
            this.xx.showSidebarAllocate = true;
            $.getJSON('/planner/data/trade/joballocate', function (sites) {
                this.xx.sel_joballocate = sites;
            }.bind(this));
        },
        gotoURL: function (url) {
            postAndRedirect(url, this.xx.params);
        },
        changeWeek: function (date) {
            this.xx.params.date = date;
            postAndRedirect('/planner/trade', this.xx.params);
        },
        changeWeekTrans: function (date) {
            this.xx.params.date = date;
            postAndRedirect('/planner/transient', this.xx.params);
        },
        changeWeekSelected: function () {
            //alert(this.xx.week_selected);
            this.xx.params.date = moment(this.xx.mon_now).add(this.xx.week_selected, 'days').format('YYYY-MM-DD');
            postAndRedirect('/planner/trade', this.xx.params);
        },
        weeklyHeader: function (date, days) {
            console.log('hh');
            /*
            if (moment(date).month() == moment(date).days(5).month())
                return moment(date).format('MMMM DD') + ' - ' + moment(date).days(5).format('DD') + moment(date).format(', YYYY');
            else
                return moment(date).format('MMM DD') + ' - ' + moment(date).days(5).format('MMM DD') + moment(date).format(', YYYY');
                */
            return moment(date).add(days, 'days').format('MMM DD') + ' - ' + moment(date).add(days, 'days').days(5).format('MMM DD') + moment(date).add(days, 'days').format(', YYYY');
        },
        weekDateHeader: function (date, days) {
            return moment(date).add(days, 'days').format('DD/MM');
        },
        weekDate: function (date, days) {
            return moment(date).add(days, 'days').format('YYYY-MM-DD');
        },
        pastDate: function (date) {
            // determine if given date is or before today
            return (date.match(/^\d{4}-\d{2}-\d{2}$/)) ? moment(date).isSameOrBefore(moment(), 'day') : false;
        },
        todayDate: function (date) {
            // determine if given date is today
            return moment(date).isSame(moment(), 'day');
        },
        showNewTask: function () {
            this.xx.showNewTask = true;
            // Hack - set day_eid fror eid2 because eid not set on initial load of sidebar
            this.xx.day_eid = this.xx.day_eid2;
        },
        getSites: function () {
            $.getJSON('/planner/data/sites', function (sites) {
                this.xx.sites = sites;
                this.xx.sites.unshift({value: '', text: 'Select Site'});
                this.getPlan();
            }.bind(this));
        },
        getPlan: function () {
            this.xx.mon_this = moment().day(1).format('YYYY-MM-DD');

            setTimeout(function () {
                this.xx.load_plan = true;
                $.getJSON('/planner/data/weekly/' + this.xx.mon_now + '/alltrade', function (data) {
                    this.xx.plan = data[0];
                    //this.xx.non_rostered = data[1];
                    this.xx.maxjobs = data[2];
                    this.xx.leave = data[3];
                    //this.xx.entity_all_onsite = data[4];
                    this.xx.sel_super = data[5];
                    this.xx.permission = data[6];
                    // remove 'All Sites' from sel_super
                    var obj_all = objectFindByKey(this.xx.sel_super, 'value', 'all');
                    this.xx.sel_super.$remove(obj_all);
                    this.xx.sel_super.unshift({value: '', text: 'Select Supervisor'})

                    this.xx.load_plan = false;
                    this.$broadcast('refreshWeekPlanEvent');
                }.bind(this));

                // Get possible companies that are skilled in given trade
                this.getCompanyForTrade();

                // Get upcoming tasks for given trade
                $.getJSON('/planner/data/trade/upcoming/' + this.xx.mon_now, function (data) {
                    this.xx.upcoming_task = data[0];
                    this.xx.upcoming_plan = data[1];
                }.bind(this));
            }.bind(this), 100);
        },
        getCompanyForTrade: function () {
            // Get possible companies that are skilled in given trade
            if (this.xx.params.trade_id) {
                $.getJSON('/planner/data/company/trade/' + this.xx.params.trade_id, function (companies) {
                    this.xx.companies = companies;
                    this.xx.trade_name = $('#trade_id option:selected').html();
                    // Add the generic trade to top of the company array
                    this.xx.companies.unshift({entity: 't.' + this.xx.params.trade_id, type: 't', id: this.xx.params.trade_id, name: this.xx.trade_name})
                }.bind(this));
            }
        },
        countUpcoming: function (trade_id) {
            var count = 0;
            for (var i = 0; i < this.xx.upcoming_plan.length; i++) {
                if (this.xx.upcoming_plan[i].trade_id == trade_id)
                    count = count + 1;
            }
            return count;
        },
        assignSiteAndTradeOptions: function () {
            // Get possible trades that the given entity can do.
            this.xx.showAssign = true;
            this.xx.day_eid = this.xx.day_eid2; // Hack assign eid from eid2

            if (this.xx.day_etype == 't') {
                // Entity is a generic trade so can easy determine companies that are skilled in given trade
                this.xx.assign_trade = this.xx.day_eid;
                //this.assignCompanyOptions();
            } else if (this.xx.day_etype == 'c') {
                // Enitity is a company so many be skilled in multiple trades so get list of possible trades
                $.getJSON('/planner/data/company/' + this.xx.day_eid + '/trades', function (trades) {
                    this.xx.sel_trade = trades;
                    // Set assign_trade if company only skilled 1 trade
                    if (trades.length === 2) {
                        this.xx.assign_trade = trades[1].value;
                        //this.assignCompanyOptions();
                    }
                }.bind(this));
            }

            // Create list of current site Entity on for the day to reassign
            this.xx.sel_site = [{value: '', text: 'Select Site'}];
            for (var i = 0; i < this.xx.day_plan.length; i++) {
                if (!objectFindByKey(this.xx.sel_site, 'value', this.xx.day_plan[i]['site_id'])) {
                    var site = objectFindByKey(this.xx.sites, 'id', this.xx.day_plan[i]['site_id']);
                    this.xx.sel_site.push({value: this.xx.day_plan[i]['site_id'], text: site.name});
                }
            }
        },
        assignCompanyOptions: function () {
            // Get possible companies that are skilled in given trade
            $.getJSON('/planner/data/company/match-trade/trade/' + this.xx.assign_trade + '/site/' + this.xx.assign_site, function (companies) {
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
            if (this.xx.assign_type == 't')
                this.xx.assign_cid = this.xx.assign_trade;

            // If Assigning Upcoming Task - add it to current planner if not present so it can be assigned.
            if (this.xx.showSidebarUpcoming && !objectFindByKey(this.xx.plan, 'id', this.xx.day_upcoming.id)) {
                this.xx.plan.push(this.xx.day_upcoming);
                this.xx.showSidebarUpcoming = false;
            }

            if (this.xx.assign_tasks == 'all') {
                // Before we can assign 'all' future tasks we need to import the specific site plan into trade planner
                // Delay assigning tasks just a sec to enable us to display loading spinner first
                this.xx.showSidebarUpcoming = false;
                this.xx.load_plan = true;
                setTimeout(function () {
                    // Import Site into current planner so we can manipulate it.
                    importSite(this.xx.plan, this.xx.assign_site)
                        .then(function (result) {
                            assignTasksFromDate(this.xx.plan, this.xx.assign_site, this.xx.assign_type, this.xx.assign_cid, this.xx.assign_cname, this.xx.assign_tasks, this.xx.assign_trade, this.xx.day_date)
                                .then(function (result) {
                                    if (result) {
                                        this.xx.day_etype = '';
                                        this.xx.day_ename = '';
                                        this.xx.load_plan = false;
                                        this.$broadcast('refreshWeekPlanEvent');
                                        console.log('refreshed planner');
                                    }
                                }.bind(this));
                        }.bind(this));
                }.bind(this), 100);
            } else {
                assignTasksFromDate(this.xx.plan, this.xx.assign_site, this.xx.assign_type, this.xx.assign_cid, this.xx.assign_cname, this.xx.assign_tasks, this.xx.assign_trade, this.xx.day_date)
                    .then(function (result) {
                        if (result) {
                            this.xx.day_etype = '';
                            this.xx.day_ename = '';
                            this.$broadcast('refreshWeekPlanEvent');
                            console.log('refreshed planner');
                        }
                    }.bind(this));
            }

            setTimeout(function () {
                this.$broadcast('refreshWeekPlanEvent');
                console.log('delayed refreshed planner');
            }.bind(this), 3000);


        },
        addTask: function () {
            // Add task to planner
            this.xx.showNewTask = false;

            //alert('adding task');
            // Create new task
            var newtask = {
                id: '', site_id: this.xx.day_site_id, entity_type: this.xx.day_etype, entity_id: this.xx.day_eid,
                entity_name: this.xx.day_ename, task_id: this.xx.day_task_id,
                task_code: '', task_name: 'Task Unassigned', trade_id: '', trade_name: '',
                from: this.xx.day_date, to: this.xx.day_date, days: 1
            }
            console.log('adding new task:' + newtask.task_name + ' id:' + newtask.task_id + ' and assigned to t:' + newtask.entity_type + ' id:' + newtask.entity_id);

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
            // Check for special cases ie 'START' + 'STARTCarp' + 'Prac Complete' and prevent duplicate task
            var validTask = true;
            var site = objectFindByKey(this.xx.sites, 'id', this.xx.day_site_id);

            // Don't allow any task to be added prior to 'START' task (except Pre-Construction meeting)
            if (site.start != '' && moment(this.xx.day_date).isBefore(site.start)) {
                validTask = false;
                toastr.error('Unable to add tasks before "Start Job"');
            } else if (newtask.task_code === 'START') {
                toastr.error("This task can only be added by the Trade Planner Actions button");
                validTask = false;
            }
            // Only allow STARTCarp + Carp Prac task to be added on Site Planner for speed performance reason
            if (newtask.task_code === 'STARTCarp' || newtask.task_id == '5') {
                toastr.error("This task can only be added on the Site Planner");
                validTask = false;
            }

            if (validTask) {
                // Add new task to DB 'then' if successful add to planner
                addTaskDB(newtask).then(function (result) {
                    if (result) {
                        newtask.id = result.id;
                        this.xx.plan.push(newtask);
                        this.xx.day_plan.unshift(newtask);
                        this.xx.day_task_id = '';
                        this.$broadcast('refreshWeekPlanEvent');
                        //this.xx.connected_tasks = connectedTasks(this.xx.plan, this.xx.params.site_id, this.xx.day_etype, this.xx.day_eid, this.xx.day_date);
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
                    // Check for special cases ie 'START' + 'STARTCarp'
                    if (task.task_code === 'START') this.xx.start_date = '';
                    if (task.task_code === 'STARTCarp') this.xx.start_carp = '';

                    // Remove task from planners
                    console.log('Removing task from planners  task:' + task.id + ' F:' + task.from + ' T:' + task.to + ' Days:' + task.days + ' EID:' + task.entity_id);
                    this.xx.day_plan.$remove(task);
                    this.xx.plan.$remove(task);
                    //this.xx.connected_tasks = connectedTasks(this.xx.plan, this.xx.params.site_id, this.xx.day_etype, this.xx.day_eid, this.xx.day_date);
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
        moveTaskToDate: function (task, date) {
            // Move task to given date - moves whole task from 'from' date. Doesn't split tasks
            // Used for Upcoming Tasks
            if (date) {
                // if 'START' task then move whole job + attempt to also move tasks
                // on the day before with it. ie load job + scaffold etc
                if (task.task_code === 'START') {
                    this.xx.showSidebarUpcoming = false;
                    this.xx.load_plan = true;

                    // Delay moving of job just a sec to enable us to display loading spinner first
                    setTimeout(function () {
                        // Import Site into current planner so we can manipulate it.
                        importSite(xx.plan, task.site_id)
                            .then(function (result) {
                                var direction = '-';
                                var days = workDaysBetween(task.from, date) - 1;
                                var dayBefore = nextWorkDate(task.from, '-', '1');
                                // Prevent job being moved to or before today
                                if (direction === '-' && moment(moment(dayBefore).subtract(days, 'day')).isSameOrBefore(moment(), 'day')) {
                                    toastr.error('Unable to move tasks to or before today!' + date + direction + days);
                                    this.xx.load_plan = false;
                                } else {
                                    moveJobFromDate(this.xx.plan, task.site_id, dayBefore, direction, days)
                                        .then(function (result) {
                                            if (result) {
                                                this.xx.upcoming_plan.$remove(task);
                                                this.day_date = task.from; // update day_date to help upcoming assign task function
                                                this.xx.load_plan = false;
                                                this.$broadcast('refreshWeekPlanEvent');
                                            }
                                        }.bind(this));
                                }
                            }.bind(this));
                    }.bind(this), 100);

                } else {
                    var copyTask = jQuery.extend({}, task) // copy task
                    moveTasktoDate(this.xx.plan, copyTask, date).then(function (result) {
                        if (result) {
                            task.id = result.id;
                            task.from = result.from;
                            task.to = result.to;
                            task.days = result.days;
                            this.xx.upcoming_plan.$remove(task);
                            this.day_date = task.from; // update day_date to help upcoming assign task function
                            this.$broadcast('refreshWeekPlanEvent');
                            //console.log('moved task to new date');
                        }
                    }.bind(this));
                }
            }
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
                var dayBefore = nextWorkDate(task.from, '-', '1')
                var dayAfterToday1 = nextWorkDate(this.xx.today, '+', '1')
                var dayAfterToday2 = nextWorkDate(this.xx.today, '+', '2')
                if (direction === '-' && moment(dayBefore).isSameOrBefore(moment(), 'day'))
                    toastr.error('Unable to move tasks to or before today!!!');
                else if (direction === '-')
                    this.moveJobFromDate(task.site_id, dayAfterToday2, '-', 1);
                else
                    this.moveJobFromDate(task.site_id, dayAfterToday1, '+', 1);

                this.xx.enableActions = false;
            } else {
                // Disable Sidebar actions just a sec to ensure user doesn't spam buttons
                this.xx.enableActions = false;
                setTimeout(function () {
                    this.xx.enableActions = true;
                }.bind(this), 500);

                moveTaskFromDate(this.xx.plan, task, this.xx.day_date, direction, days).then(function (result) {
                    if (result) {
                        if (task.id != result.id)
                            this.xx.showSidebar = false;
                        else
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
        moveJobFromDate: function (site_id, date, direction, days) {
            // Prevent job being moved to before today
            if (direction === '-' && moment(moment(date).subtract(days, 'day')).isSameOrBefore(moment(), 'day')) {
                toastr.error('Unable to move tasks to or before today!' + date + direction + days);
                this.xx.load_plan = false;
            } else {
                // Delay moving of job just a sec to enable us to display loading spinner first
                this.xx.load_plan = true;
                setTimeout(function () {
                    $.getJSON('/planner/data/site/' + site_id, function (plan) {
                        this.xx.plan = plan[1];
                        moveJobFromDate(this.xx.plan, site_id, date, direction, days)
                            .then(function (result) {
                                if (result) {
                                    this.$broadcast('refreshWeekPlanEvent');
                                    this.getPlan();
                                }
                            }.bind(this));
                    }.bind(this))
                }.bind(this), 100);
            }
        },
        moveEntityFromDate: function (site_id, date, direction, days) {
            // Prevent entity being moved to or before today
            if (direction === '-' && moment(nextWorkDate(date, direction, days)).isSameOrBefore(moment(), 'day')) {
                toastr.error('Unable to move tasks to or before today');
            } else {
                this.xx.showSidebar = false;
                // Delay moving of entity just a sec to enable us to display loading spinner first
                this.xx.load_plan = true;
                setTimeout(function () {
                    //alert('date:'+date+' '+direction+days+' days');
                    var site = objectFindByKey(this.xx.day_sites, 'site_id', site_id);
                    moveEntityFromDate(this.xx.plan, site.connected_tasks, date, direction, days)
                        .then(function (result) {
                            if (result) {
                                this.xx.load_plan = false;
                                this.$broadcast('refreshWeekPlanEvent');
                            }
                        }.bind(this));
                }.bind(this), 100);
            }
        },
        deleteConnectedTasks: function (site_id) {
            // Delete all connected tasks from a given date
            var site = objectFindByKey(this.xx.day_sites, 'site_id', site_id);
            console.log(site.site_name);
            for (var i = 0; i < site.connected_tasks.length; i++) {
                var task = site.connected_tasks[i];

                // If task begins before given date then delete only from after today ie. update the 'to' + 'days'
                if (moment(xx.day_date).isBetween(task.from, task.to, null, '(]')) {
                    task.to = nextWorkDate(xx.day_date, '-', 1);
                    task.days = workDaysBetween(task.from, task.to);
                    console.log('deleted part task id:[' + task.id + '] ' + task.task_name + ' F:' + task.from + ' T:' + task.to + ' days:' + task.days);
                    updateTaskDB(task);
                    toastr.warning('Only deleted task after today');
                    this.$broadcast('refreshWeekPlanEvent');
                } else
                    deleteTaskDB(task)
                        .then(function (result) {
                            // Remove task from planners
                            console.log('Removing task from planner  task:' + result.id + ' F:' + result.from + ' T:' + result.to + ' Days:' + result.days + ' EID:' + result.entity_id);
                            this.xx.plan.$remove(result);
                            this.$broadcast('refreshWeekPlanEvent');
                        }.bind(this));
            }
            this.$broadcast('refreshWeekPlanEvent');
            this.xx.showSidebar = false;
            toastr.success('Deleted Connected Tasks');

        },
        validJobstart: function () {
            // Verifify 'site' + 'date' option are set
            var arr = this.xx.jobstart.split('/');
            var jobstart = arr[2] + '-' + arr[1] + '-' + arr[0];
            if (this.xx.assign_site && moment(jobstart).isAfter(this.xx.today, 'day') && moment(this.xx.jobstart, 'DD/MM/YYYY', true).isValid())
                return true;
            return false;
        },
        saveJobstart: function () {
            this.xx.showSidebarAddstart = false;

            // Add new 'START' task to site
            var arr = this.xx.jobstart.split('/');
            var jobstart = arr[2] + '-' + arr[1] + '-' + arr[0];

            addStartTaskToPlanner(this.xx.plan, this.xx.assign_site, jobstart);

            setTimeout(function () {
                this.$broadcast('refreshWeekPlanEvent');
            }.bind(this), 3000);
        },

        moveJobstart: function () {
            // Move 'START' task on site
            this.xx.showSidebarMovestart = false;
            // Split new start
            var arr = this.xx.jobstart.split('/');
            var new_start = arr[2] + '-' + arr[1] + '-' + arr[0];
            // Split old start
            //var obj = objectFindByKey(this.xx.sel_jobstart, 'value', this.xx.assign_site);
            //var arr2 = obj.text.slice(-10).split('/'); //this.xx.assign_site; //obj.text;
            //var old_start = arr2[2] + '-' + arr2[1] + '-' + arr2[0];
            var obj = objectFindByKey(this.xx.sites, 'id', this.xx.assign_site);
            var old_start = obj.start;
            var old_first = obj.first;
            var old_first_id = obj.first_id;

            var old_date = moment(old_start);
            var new_date = moment(new_start);

            if (!old_date.isSame(new_date)) {
                if (new_date.isBefore(old_date))
                    var direction = '-';
                else
                    var direction = '+'
                var days = workDaysBetween(old_start, new_start) - 1;
                var days_between = Math.abs(moment(new_start).diff(moment(old_start), 'days'));
                //alert('old:' + old_start + ' new:' + new_start + ' days:' + days + ' dir:' + direction + ' diff:'+days_between);
                if (direction == '-' && moment(moment(old_first).subtract(days_between, 'day')).isSameOrBefore(moment(), 'day')) {
                    // The new date for the first task 'Pre-Construction' will be before today so manually make it tomorrow
                    // and move the START task to the selected new date
                    this.moveJobFromDate(this.xx.assign_site, old_start, direction, days);

                    var dayAfterToday1 = nextWorkDate(this.xx.today, '+', '1')
                    //alert('short:'+dayAfterToday1);
                    getTaskDB(obj.first_id).then(function (task) {
                        if (task) {
                            console.log('*** id:' + task.id + ' F:' + task.from + ' new:' + dayAfterToday1);
                            task.from = dayAfterToday1;
                            task.to = dayAfterToday1;
                            updateTaskDB(task).then(function (result) {
                                if (result) {
                                    this.$broadcast('refreshWeekPlanEvent');
                                    this.getPlan();
                                    obj.first = dayAfterToday1;
                                }
                            }.bind(this));
                        }
                    }.bind(this));
                } else
                    this.moveJobFromDate(this.xx.assign_site, old_first, direction, days);

                // Email Jobstart
                var data = {site_id: this.xx.assign_site, newdate: new_start, olddate: old_start};
                $.ajax({
                    url: '/planner/data/trade/email-jobstart',
                    type: 'POST',
                    data: data,
                    success: function (result) {
                        console.log('Emailed Jobstart');
                        //console.log('Emailed Jobstart site_id:'+this.xx.assign_site+ 'newdate:'+new_start+ ' oldate:'+old_start);
                    },
                    error: function (result) {
                        alert("Failed to email Job Start");
                        console.log('Email of Job Start FAILED');
                    }
                });
            } else
                toastr.warning('Selected date was same as old one');

            setTimeout(function () {
                this.$broadcast('refreshWeekPlanEvent');
            }.bind(this), 3000);
        },
        validSiteAllocate: function () {
            // Verify 'site' + 'super' options are set
            if (this.xx.assign_site && this.xx.assign_super)
                return true;
            return false;
        },
        saveSiteAllocate: function () {
            // Allocate a site to a supervisor
            allocateSiteToSupervisor(this.xx.assign_site, this.xx.assign_super)
                .then(function (result) {
                    if (result) {
                        this.getPlan();
                        toastr.success('Allocated site to supervisor');
                    }
                }.bind(this), function (err) {
                    console.log('unable to allocate site to supervisor');
                });
            this.xx.showSidebarAllocate = false;
        },
    },
});

Vue.component('app-company', {
    props: ['etype', 'eid', 'ename', 'ecompliant'],
    template: '#company-template',

    data: function () {
        return {xx: xx};
    },
    events: {
        refreshWeekPlanEvent: function () {
            // Refresh planner for given site
            this.$broadcast('refreshDayPlanEvent')
        },
    },
    methods: {
        weekDate: function (date, days) {
            return moment(date).add(days, 'days').format('YYYY-MM-DD');
        },
        leaveSummary: function () {
            // Determine if Company is on leave and return summary
            if (this.etype === 'c' && this.xx.leave[this.eid])
                return this.xx.leave[this.eid]['summary'];
            return false;
        },
        compliant: function () {
            // Determine if Company is compliant
            //if (this.etype === 'c' && this.xx.leave[this.eid])
                //return this.xx.leave[this.eid]['summary'];
            //return false;
            //return 'PP'+this.ecompliant+'LL'+this.eid;
            return false;
        },
        cellBG: function (date, days) {
            var str = '';
            var date = moment(date).add(days, 'days').format('YYYY-MM-DD');

            if (this.etype === 'c' && moment(date).isSameOrAfter(this.xx.today, 'day') && this.xx.leave[this.eid] && this.xx.leave[this.eid][date])
                str = str + ' leaveBG';
            else if (this.etype === 'c' && moment(date).isBefore(this.xx.today, 'day') && this.xx.leave[this.eid] && this.xx.leave[this.eid][date])
                str = str + ' pastleaveBG';
            else if (moment(date).isSame(this.xx.today, 'day'))
                str = ' todayBG'

            return str;
        },
    },

});

Vue.component('app-dayplan', {
    props: ['date', 'etype', 'eid', 'ename', 'ecompliant'],
    template: '#dayplan-template',

    created: function () {
        this.getEntityPlan();
    },
    data: function () {
        return {
            conflicts: '', onleave: false,
            entity_plan: [],
            entity_sites: [],
            xx: xx,
        };
    },
    events: {
        refreshDayPlanEvent: function () {
            //alert('refresh day '+this.date);
            this.getEntityPlan();
        },
    },
    filters: {
        max15chars: function (str) {
            return str.substring(0, 15);
        },
    },
    methods: {
        openSidebar: function (date) {
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
            this.xx.day_site_id = '';
            this.xx.day_task_id = '';
            this.xx.day_conflicts = this.conflicts;
            this.xx.assign_cid = '';
            this.xx.assign_cname = '';
            this.xx.assign_site = '';
            this.xx.assign_trade = '';
            this.xx.assign_tasks = '';
            this.xx.sel_site = [];
            this.xx.sel_trade = [];
            this.getTaskOptions();
            this.getConnectedTasks();
        },
        getEntityPlan: function () {
            // Get plan for current Entity
            this.entity_plan = [];
            this.entity_sites = [];

            // Search Site Plan for Entity tasks on specific date
            for (var i = 0; i < this.xx.plan.length; i++) {
                var task = this.xx.plan[i];
                if (task.entity_type == this.etype & task.entity_id == this.eid) {
                    // Allow tasks that span multiple days
                    if (moment(this.date).isBetween(task.from, task.to, null, '[]')) {
                        // Add Site name to task object + add to Entity Plan
                        var site_name = '???';
                        var site = objectFindByKey(this.xx.sites, 'id', task.site_id);
                        if (site) {
                            //console.log('tid:' + task.id + ' tsid:' + task.site_id + ' sid:' + site.id + ' name:' + site.name);
                            site_name = site.name;
                        } else
                            console.log('tid:' + task.id + ' tsid:' + task.site_id + ' name:' + site_name);

                        task.site_name = site_name;
                        this.entity_plan.push(task);

                        // Verify if unique then add to site array
                        var result = objectFindByKey(this.entity_sites, 'site_id', task.site_id);

                        if (result) {
                            result.tasks = result.tasks + ', ' + task.task_code;
                        } else {
                            var obj = {
                                site_id: task.site_id,
                                site_name: site_name,
                                entity_type: task.entity_type,
                                entity_id: task.entity_id,
                                entity_name: task.entity_name,
                                tasks: '', conflicts: '', leave: ''
                            };

                            if (task.task_code === 'START' || task.task_code === 'STARTCarp')
                                obj.tasks = '<span class="label label-info" style="font-size:10px">' + task.task_code + '</span>';
                            else
                                obj.tasks = task.task_code;

                            // Determine if Company has exceed 'maxjobs' for given date;
                            if (obj.entity_type === 'c' && this.xx.maxjobs[obj.entity_id]) {
                                if (this.xx.maxjobs[obj.entity_id][this.date]) {
                                    obj.conflicts = this.xx.maxjobs[obj.entity_id][this.date];
                                    this.conflicts = this.xx.maxjobs[this.eid][this.date];
                                }
                            }
                            this.entity_sites.push(obj);
                        }
                    }
                }
            }

            // Determine if Company is on leave
            if (this.etype === 'c' && this.xx.leave[this.eid]) {
                if (this.xx.leave[this.eid][this.date]) {
                    this.onleave = this.xx.leave[this.eid][this.date];
                }
            }
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
        getConnectedTasks: function () {
            this.xx.day_sites = [];
            //this.xx.day_sites = this.entity_sites;

            for (var i = 0; i < this.entity_sites.length; i++) {
                var site = this.entity_sites[i];
                this.xx.day_sites.push({
                    site_id: site.site_id,
                    site_name: site.site_name,
                    connected_tasks: connectedTasks(this.xx.plan, site.site_id, this.xx.day_etype, this.xx.day_eid, this.xx.day_date)
                });
            }
            //this.xx.connected_tasks = connectedTasks(this.xx.plan, this.xx.params.site_id, this.etype, this.eid, date);
        },
        assignCompanyOptions: function () {
            // Get possible companies that are skilled in given trade
            $.getJSON('/planner/data/company/match-trade/trade/' + this.xx.assign_trade + '/site/any', function (companies) {
                this.xx.sel_company = companies;
            }.bind(this));
        },
        pastDate: function (date) {
            // determine if given date is or before today
            if (moment(date).isSameOrBefore(moment(), 'day'))
                return true;
            return false;
        },
        pastDateTrade: function (date) {
            // determine if given date is or before today
            if (moment(date).isSameOrBefore(moment(), 'day') || this.xx.permission == 'view')
                return true;
            return false;
        },
        entityClass: function (entity) {
            // Set class of task name for displaying on planner
            var str = '';
            if (entity.entity_type === 't')
                str = str + ' font-yellow-gold';

            if (entity.entity_type === 'c' && entity.conflicts != '')
                str = str + ' font-green-jungle';

            //if (entity.entity_type === 'c' && entity.leave != '')
            //    str = str + ' label label-warning';

            return str;
        },
    },
});

var myApp = new Vue({
    el: 'body',
    data: {xx: xx},
});
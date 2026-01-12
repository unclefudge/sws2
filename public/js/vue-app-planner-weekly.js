$('#supervisor_id').change(function () {
    xx.params.supervisor_id = $(this).val();
    postAndRedirect('/planner/weekly', xx.params);
});

var xx = {
    dev: dev, permission: '', user_company_id: '',
    params: {date: '', supervisor_id: '', site_id: '', site_start: '', trade_id: '', _token: $('meta[name=token]').attr('value')},
    search: '', show_contact: '', load_plan: false, today: moment().format('YYYY-MM-DD'), week_selected: '', holidays: '',
    mon_now: '', mon_this: '', mon_prev: '', mon_next: '',
    sel_super: [], maxjobs: [], leave: [], sites: [],
    plan: [], non_rostered: [], entity_all_onsite: [],
};

Vue.component('app-weekly', {
    props: ['mondate'],
    template: '#weekly-template',
    created: function () {
        this.getSites();
    },
    data: function () {
        return {xx: xx};
    },
    methods: {
        weeklyHeader: function (date, days) {
            /*if (moment(date).month() == moment(date).days(5).month())
                return moment(date).format('MMMM DD') + ' - ' + moment(date).days(5).format('DD') + moment(date).format(', YYYY');
            else
                return moment(date).format('MMM DD') + ' - ' + moment(date).days(5).format('MMM DD') + moment(date).format(', YYYY');*/
            return moment(date).add(days, 'days').format('MMM DD') + ' - ' + moment(date).add(days, 'days').days(5).format('MMM DD') + moment(date).add(days, 'days').format(', YYYY');
        },
        weekDateHeader: function (date, days) {
            return moment(date).add(days, 'days').format('DD/MM');
        },
        weekDate: function (date, days) {
            return moment(date).add(days, 'days').format('YYYY-MM-DD');
        },
        getSites: function () {
            $.getJSON('/planner/data/sites', function (sites) {
                this.xx.sites = sites;
                this.getPlan();
            }.bind(this));
        },
        getPlan: function () {
            this.xx.mon_this = moment().day(1).format('YYYY-MM-DD');

            setTimeout(function () {
                this.xx.load_plan = true;
                $.getJSON('/planner/data/weekly/' + this.xx.mon_now, function (data) {
                    this.xx.plan = data[0];
                    this.xx.non_rostered = data[1];
                    this.xx.maxjobs = data[2];
                    this.xx.leave = data[3];
                    this.xx.entity_all_onsite = data[4];
                    this.xx.sel_super = data[5];
                    this.xx.permission = data[6];
                    this.xx.holidays = data[7];
                    this.xx.load_plan = false;
                    this.$broadcast('refreshSitePlanEvent');
                }.bind(this));
            }.bind(this), 100);
        },
        updateSupervisor: function () {
            //alert('super '+this.xx.params.supervisor_id);
        },
        changeWeek: function (date) {
            this.xx.params.date = date;
            postAndRedirect('/planner/weekly', this.xx.params);
        },
        gotoURL: function (url) {
            postAndRedirect(url, this.xx.params);
        },
    },
});

Vue.component('app-site', {
    props: ['site_id', 'site_name', 'site_code', 'site_contact', 'site_address', 'site_status', 'site_preconstruct', 'site_order', 'site_prac_complete'],
    template: '#site-template',

    data: function () {
        return {xx: xx};
    },
    events: {
        refreshSitePlanEvent: function () {
            // Refresh planner for given site
            //alert('refresh site plan' + this.site_id);
            this.$broadcast('refreshDayPlanEvent')
        },
    },
    filters: {
        max20chars: function (str) {
            return str.substring(0, 20);
        },
    },
    methods: {
        weekDate: function (date, days) {
            return moment(date).add(days, 'days').format('YYYY-MM-DD');
        },
        preConstruct: function (jobstart) {
            if (moment(jobstart).isSameOrAfter(this.xx.mon_now))
                return moment(jobstart).format('DD/MM/YYYY');
            return false;
            //alert(jobstart + ' : ' + this.xx.mon_now);
            //return 1;
        },
        viewSitePlan: function (site) {
            this.xx.params.site_id = site;
            //var selected_site = objectFindByKey(this.xx.sites, 'id', site);
            //if (selected_site.status == -1)
            //    postAndRedirect('/planner/preconstruction', this.xx.params);
            //else
            postAndRedirect('/planner/site', this.xx.params);
        },
        showSite: function (site_id) {
            // Need to determine of User is from CapeCod to either hide/show maintenance sites
            var site = objectFindByKey(this.xx.sites, 'id', site_id);
            //if (allowed_site_status.includes(obj.status)) {
            if (this.xx.user_company_id == 3) {
                if (this.xx.params.supervisor_id === 'all' && site.status == 1)
                    return this.siteSearch(site); //return true;
            } else if (this.xx.params.supervisor_id === 'all')
                return this.siteSearch(site); //return true;

            if (this.xx.params.supervisor_id === 'maint' && site.status == 2)
                return this.siteSearch(site); //return true;

            if (this.xx.params.supervisor_id === 'prac' && site.order == 3)
                return this.siteSearch(site); //return true;

            var show = false;
            if (site.supervisors.hasOwnProperty(this.xx.params.supervisor_id))
                show = true;

            if (show)
                return this.siteSearch(site); //return true;

            return false;
            //}
        },
        siteSearch: function (site) {
            if (this.xx.search != '') {
                if (site.name.toLowerCase().includes(this.xx.search.toLowerCase()))
                    return true;
                return false;
            } else
                return true;
        },
    },

});

Vue.component('app-dayplan', {
    props: ['date', 'site_id'],
    template: '#dayplan-template',

    created: function () {
        this.getDayPlan();
    },
    data: function () {
        return {
            conflicts: '', onleave: 'empty',
            day_plan: [], non_rostered: [],
            xx: xx,
        };
    },
    events: {
        refreshDayPlanEvent: function () {
            //alert('refresh day '+this.date);
            this.getDayPlan();
        },
    },
    filters: {
        max10chars: function (str) {
            if (str)
                return str.substring(0, 15);
        },
    },
    methods: {
        openSidebar: function (date) {
            // Get id + type for current Entity
        },
        viewSitePlan: function (site) {
            this.xx.params.site_id = site;
            //var selected_site = objectFindByKey(this.xx.sites, 'id', site);
            //if (selected_site.status == -1)
            //    postAndRedirect('/planner/preconstruction', this.xx.params);
            //else
            postAndRedirect('/planner/site', this.xx.params);
        },
        getDayPlan: function () {
            // Get plan for current Entity
            this.day_plan = [];

            // Search Site Plan for Entity tasks on specific date
            for (var i = 0; i < this.xx.plan.length; i++) {
                var task = this.xx.plan[i];
                if (task.site_id === this.site_id) {
                    // Allow tasks that span multiple days
                    if (moment(this.date).isBetween(task.from, task.to, null, '[]')) {
                        // Verify if unique then add to array
                        var key = task.entity_type + '.' + task.entity_id;
                        var result = objectFindByKey(this.day_plan, 'key', key);

                        if (result) {
                            result.tasks = result.tasks + ', ' + task.task_code;
                        } else {
                            var obj = {
                                key: task.entity_type + '.' + task.entity_id,
                                entity_type: task.entity_type,
                                entity_id: task.entity_id,
                                entity_name: task.entity_name,
                                maintenance: task.maintenance,
                                tasks: '', conflicts: '', leave: ''
                            };

                            if (task.task_code === 'START' || task.task_code === 'STARTCarp')
                                obj.tasks = '<span class="label label-info" style="font-size:10px">' + task.task_code + '</span>';
                            else
                                obj.tasks = task.task_code;

                            // Determine if Company has exceed 'maxjobs' for given date;
                            if (obj.entity_type === 'c' && this.xx.maxjobs[obj.entity_id]) {
                                if (this.xx.maxjobs[obj.entity_id][this.date])
                                    obj.conflicts = this.xx.maxjobs[obj.entity_id][this.date];

                            }

                            // Determine if Company is on leave
                            if (obj.entity_type === 'c' && this.xx.leave[obj.entity_id]) {
                                if (this.xx.leave[obj.entity_id][this.date])
                                    obj.leave = this.xx.leave[obj.entity_id][this.date];
                            }
                            this.day_plan.push(obj);
                        }
                    }
                }
            }

            // Sort Day Plan by key (Trades -> Company)
            this.day_plan.sort(function (a, b) {
                return (a.key < b.key) ? 1 : ((b.key < a.key) ? -1 : 0);
            });

            //
            // Non-Rostered for a specific date
            if (this.xx.non_rostered[this.site_id + '.' + this.date]) {
                this.non_rostered = this.xx.non_rostered[this.site_id + '.' + this.date];
            }

        },
        pastDate: function (date) {
            // determine if given date is before today
            if (moment(date).isBefore(moment(), 'day') && this.xx.permission === 'view') {
                return true;
            }
            return false;
        },
        entityClass: function (entity) {
            // Set class of task name for displaying on planner
            var str = '';
            if (entity.entity_type === 't')
                str = str + ' font-yellow-gold';
            // Company is All Onsite for Today
            else if (entity.entity_type === 'c' && this.date == this.xx.today && this.xx.entity_all_onsite[this.date + '.' + this.site_id + '.' + entity.entity_type + '.' + entity.entity_id] == '1')
                str = str + ' font-blue';
            // Company was NOT all Onsite for dates before Today
            else if (entity.entity_type === 'c' && moment(this.date).isBefore(this.xx.today) && this.xx.entity_all_onsite[this.date + '.' + this.site_id + '.' + entity.entity_type + '.' + entity.entity_id] == '0')
                str = str + ' font-red';
            // Company is was misplanned for dates Today or before
            else if (entity.entity_type === 'c' && moment(this.date).isSameOrBefore(this.xx.today) && this.xx.entity_all_onsite[this.date + '.' + this.site_id + '.' + entity.entity_type + '.' + entity.entity_id] == '-1')
                str = str + ' font-purple text-bold';
            // Company exceeded Max Jobs
            else if (entity.entity_type === 'c' && entity.conflicts != '' && entity.leave == '')
                str = str + ' font-green-jungle';
            // Company on Leave
            else if (entity.entity_type === 'c' && entity.leave != '')
                str = str + ' label label-sm label-warning';

            return str;
        },
        /*enitityAllOnsite: function (entity) {
         return this.xx.entity_all_onsite[this.date+'.h'+this.site_id+'.'+entity.entity_type+'.'+entity.entity_id];
         }*/
    },
})
;

var myApp = new Vue({
    el: 'body',
    data: {xx: xx},
    created: function () {
        //alert(this.mondate);
        this.getSites();
        this.getPlan();
    },
    methods: {
        weeklyHeader: function (date, days) {
            /*if (moment(date).month() == moment(date).days(5).month())
             return moment(date).format('MMMM DD') + ' - ' + moment(date).days(5).format('DD') + moment(date).format(', YYYY');
             else
             return moment(date).format('MMM DD') + ' - ' + moment(date).days(5).format('MMM DD') + moment(date).format(', YYYY');*/
            return moment(date).add(days, 'days').format('MMM DD') + ' - ' + moment(date).add(days, 'days').days(5).format('MMM DD') + moment(date).add(days, 'days').format(', YYYY');
        },
        changeWeekSelected: function () {
            //alert(this.xx.week_selected);
            this.xx.params.date = moment(this.xx.mon_now).add(this.xx.week_selected, 'days').format('YYYY-MM-DD');
            postAndRedirect('/planner/weekly', this.xx.params);
        },
        weekDateHeader: function (date, days) {
            return moment(date).add(days, 'days').format('DD/MM');
        },
        weekDate: function (date, days) {
            return moment(date).add(days, 'days').format('YYYY-MM-DD');
        },
        publicHoliday: function (date, days) {
            // determine if given date is public holiday
            caldate = moment(date).add(days, 'days').format('YYYY-MM-DD');
            if (this.xx.holidays.hasOwnProperty(caldate))
                return this.xx.holidays[caldate];

            return '';
        },
        getSites: function () {
            $.getJSON('/planner/data/sites', function (sites) {
                this.xx.sites = sites;
            }.bind(this));
        },
        getPlan: function () {
            this.xx.mon_this = moment().day(1).format('YYYY-MM-DD');

            setTimeout(function () {
                //this.xx.load_plan = true;
                $.getJSON('/planner/data/weekly/' + this.xx.mon_now + '/' + this.xx.params.supervisor_id, function (data) {
                    this.xx.plan = data[0];
                    this.xx.non_rostered = data[1];
                    this.xx.maxjobs = data[2];
                    this.xx.leave = data[3];
                    this.xx.entity_all_onsite = data[4];
                    this.xx.sel_super = data[5];
                    this.xx.permission = data[6];
                    this.xx.holidays = data[7];
                    this.xx.load_plan = false;
                    this.$broadcast('refreshSitePlanEvent');
                }.bind(this));
            }.bind(this), 100);
        },
        updateSupervisor: function () {
            //alert('super '+this.xx.params.supervisor_id);
        },
        viewWeek: function (date) {
            if (this.xx.user_company_id == '3')
                return true;
            oneWeekAway = moment(this.xx.mon_now).add(7, 'days');

            if (moment(this.xx.mon_now).add(7, 'days').isBefore(moment(this.xx.mon_this).add(this.xx.plan_ahead, 'days')))
                return true;
            //alert(oneWeekAway.format('YYYY-MM-DD'));
        },
        changeWeek: function (date) {
            this.xx.params.date = date;
            postAndRedirect('/planner/weekly', this.xx.params);
        },
        gotoURL: function (url) {
            postAndRedirect(url, this.xx.params);
        },
    },
});

setTimeout(() => {
    xx.load_plan = false;
    document.body.style.opacity = '1';
}, 4000);
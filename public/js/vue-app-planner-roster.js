$('#supervisor_id').change(function () {
    xx.params.supervisor_id = $(this).val();
    postAndRedirect('/planner/roster', xx.params);
    alert(xx.params.supervisor_id);
});

var xx = {
    dev: dev, permission: '', user_company_id: '',
    params: {date: '', supervisor_id: '', site_id: '', site_start: 'week', trade_id: '', _token: $('meta[name=token]').attr('value')},
    today: moment().format('YYYY-MM-DD'), current_date: moment().format('YYYY-MM-DD'),  week_selected: '',
    showSpinner: false,
    rostered: [], unrostered: [], plan: [], sel_super: [], sites: [],
};

Vue.component('app-attend', {
    template: '#attend-template',

    created: function () {
        this.getDayPlan();
    },
    data: function () {
        return {xx: xx};
    },
    filters: {
        formatDateFull: function (date) {
            return moment(date).format('dddd Do MMMM YYYY');
        },
        formatTime: function (time) {
            return moment('2000-01-01 ' + time).format('h:mm');
        },
        formatTime2: function (time) {
            return moment('2000-01-01 ' + time).format('h:mm a');
        },
    },
    methods: {
        gotoURL: function (url) {
            postAndRedirect(url, this.xx.params);
        },
        getDayPlan: function () {
            // Get plan from database and initialise planner variables
            setTimeout(function () {
                var current_supervisor_id = 'none';
                if (this.xx.params.supervisor_id)
                    current_supervisor_id = this.xx.params.supervisor_id;

                this.xx.showSpinner = true;
                this.xx.plan = [];
                //$.getJSON('/planner/data/site/' + current_site_id + '/roster/' + this.xx.current_date, function (plan) {
                $.getJSON('/planner/data/roster/' + this.xx.current_date + '/super/' + current_supervisor_id, function (plan) {
                    this.xx.sites = plan[0];
                    this.xx.permission = plan[1];
                    this.xx.sel_super = plan[2];
                    //this.xx.rostered = plan[1];
                    //this.xx.unrostered = plan[2];
                    this.xx.showSpinner = false;
                }.bind(this));
                this.$broadcast('refreshWeekPlanEvent');

            }.bind(this), 100);
        },
        weeklyHeader: function (date, days) {
            return moment(date).add(days, 'days').format('MMM DD') + ' - ' + moment(date).add(days, 'days').days(5).format('MMM DD') + moment(date).add(days, 'days').format(', YYYY');
        },
        changeWeekSelected: function () {
            //alert(this.xx.week_selected);
            this.xx.params.date = moment(this.xx.mon_now).add(this.xx.week_selected, 'days').format('YYYY-MM-DD');
            postAndRedirect('/planner/roster', this.xx.params);
        },
        changeDay: function (direction) {
            // Change current day to Today or go forward or backwards a day
            if (direction == 'today')
                this.xx.current_date = this.xx.today;
            else if (direction == '-')
                this.xx.current_date = moment(this.xx.current_date).subtract(1, 'days').format('YYYY-MM-DD');
            else
                this.xx.current_date = moment(this.xx.current_date).add(1, 'days').format('YYYY-MM-DD');
            this.getDayPlan();
        },
        pastDate: function (date) {
            // determine if given date is before today
            return moment(date).isBefore(moment(), 'day');
        },
        futureDate: function (date) {
            // determine if given date is after today
            return moment(date).isAfter(moment(), 'day');
        },
        updateRoster: function (user, site_id, action) {
            // Update Roster if they haven't attended site already
            if (!user.attended || !user.roster_id) {
                // Delete user from Roster
                if (action == 'del' && user.roster_id) {
                    this.$http.post('/planner/data/roster/user/' + user.roster_id, user)
                        .then(function (response) {
                            user.roster_id = 0;
                            console.log('del ' + user.name);
                        }.bind(this)).catch(function (response) {
                        alert('failed to remove user from roster');
                    });

                }
                // Add user to Roster
                if (action == 'add' && !user.roster_id) {
                    var record = {site_id: site_id, user_id: user.user_id, date: this.xx.current_date + ' 00:00:00'};
                    this.$http.post('/planner/data/roster/user/', record)
                        .then(function (response) {
                            user.roster_id = response.data.id;
                            console.log('add ' + user.name);
                        }.bind(this)).catch(function (response) {
                        alert('failed to add user to roster');
                    });
                }
            }
        },
        toggleRoster: function (user, site_id) {
            // Toggle user on Roster
            if (user.roster_id)
                this.updateRoster(user, site_id, 'del');
            else
                this.updateRoster(user, site_id, 'add')
        },
        checkall: function (entity, action) {
            for (var i = 0; i < entity.attendance.length; i++)
                this.updateRoster(entity.attendance[i], entity.site_id, action);
        },
        enitityAllOnsite: function (entity) {
            // All users that are rostered on are onsite
            var rostered = false;
            //console.log('s:'+entity.site_id+ ' c:'+entity.entity_name + ' a:'+entity.attendance.length);
            for (var i = 0; i < entity.attendance.length; i++) {
                var rec = entity.attendance[i];
                //console.log('r:'+rec.roster_id + ' a:'+rec.attended);
                if (rec.roster_id && !rec.attended)
                    return false;
                if (rec.roster_id && rec.attended)
                    rostered = true;
            }
            //console.log(rostered);
            return rostered;
        },
        enitityPlannedButNotRostered: function (entity) {
            // Company planned but no users are rostered to attend or 'ticked'
            if (entity.tasks != 'Unrostered') {
                console.log('s:'+entity.site_id+ ' c:'+entity.entity_name + ' a:'+entity.attendance.length);
                if (entity.attendance.length == 0)
                    return true;

                for (var i = 0; i < entity.attendance.length; i++) {
                    var rec = entity.attendance[i];
                    console.log('r:'+rec.roster_id + ' a:'+rec.attended);
                    if (rec.roster_id && !rec.attended)
                        return false;
                }
                return true;
            }
            return false;
        },
        entityClass: function (entity) {
            // Set class of task name for displaying on planner
            var str = '';
            if (entity.entity_type === 't')
                str = str + ' font-yellow-gold';

            /*if (entity.entity_type === 'c' && entity.allonsite && entity.attendance.length == 0)
             str = str + ' font-purple';

             if (entity.entity_type === 'c' && entity.allonsite)
             str = str + ' font-blue';*/

            if (entity.entity_type === 'c' && this.enitityAllOnsite(entity))
                str = str + ' font-blue';
            else if (entity.entity_type === 'c' && this.enitityPlannedButNotRostered(entity))
                str = str + ' font-purple';

            return str;
        },
    },
});

var myApp = new Vue({
    el: 'body',
    data: {xx: xx},
});
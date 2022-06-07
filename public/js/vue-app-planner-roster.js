$('#site_id').change(function () {
    xx.params.site_id = $(this).val();
    alert(xx.params.site_id);
    postAndRedirect('/planner/attendance', xx.params);
});

var xx = {
    dev: dev, permission: '', user_company_id: '',
    params: {date: '', supervisor_id: '', site_id: '', site_start: 'week', trade_id: '', _token: $('meta[name=token]').attr('value')},
    today: moment().format('YYYY-MM-DD'), current_date: moment().format('YYYY-MM-DD'),
    showSpinner: false,
    rostered: [], unrostered: [], plan: [], sel_site: [], sites: [],
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
                var current_site_id = 'none';
                if (this.xx.params.site_id)
                    current_site_id = this.xx.params.site_id;

                    this.xx.showSpinner = true;
                    this.xx.plan = [];
                    $.getJSON('/planner/data/site/' + current_site_id + '/roster/' + this.xx.current_date, function (plan) {
                        this.xx.sites = plan[0];
                        //this.xx.rostered = plan[1];
                        //this.xx.unrostered = plan[2];
                        //this.xx.sel_site = plan[3];
                        this.xx.permission = plan[1];
                        this.xx.showSpinner = false;
                    }.bind(this));
                    this.$broadcast('refreshWeekPlanEvent');

            }.bind(this), 100);
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
                            //console.log('del '+user.name);
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
                            //console.log('add '+user.name);
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
                this.updateRoster(entity.attendance[i], action);
        },
        enitityAllOnsite: function (entity) {
            // All users that are rostered on are onsite
            for (var i = 0; i < this.xx.rostered.length; i++) {
                var rec = this.xx.rostered[i];
                if (rec.key == entity.key) {
                    var rostered = false;
                    for (var x = 0; x < rec.attendance.length; x++) {
                        if (rec.attendance[x]['roster_id'] && !rec.attendance[x]['attended'])
                            return false;
                        if (rec.attendance[x]['roster_id'] && rec.attendance[x]['attended'])
                            rostered = true;
                    }

                    // If company rostered but all are 'un-ticked' to attend then company isn't all onsite
                    if (!rostered)
                        return false ;
                }
            }
            return true;
        },
        enitityPlannedButNotRostered: function (entity) {
            // Company planned but no users are rostered to attend or 'ticked'
            for (var i = 0; i < this.xx.rostered.length; i++) {
                var rec = this.xx.rostered[i];
                if (rec.key == entity.key) {
                    var rostered = false;
                    for (var x = 0; x < rec.attendance.length; x++) {
                        if (rec.attendance[x]['roster_id'])
                            return false;
                    }
                    return true ;
                }
            }
            return false;
        },
        entityClass: function (entity) {
            // Set class of task name for displaying on planner
            var str = '';
            if (entity.entity_type === 't')
                str = str + ' font-yellow-gold';

            if (entity.entity_type === 'c' && this.enitityAllOnsite(entity))
                str = str + ' font-blue';

            if (entity.entity_type === 'c' && this.enitityPlannedButNotRostered(entity))
                str = str + ' font-purple';

            return str;
        },
    },
});

var myApp = new Vue({
    el: 'body',
    data: {xx: xx},
});
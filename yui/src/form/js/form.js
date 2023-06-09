"use strict";

M.availability_skillpoints = M.availability_skillpoints || {};

M.availability_skillpoints.form = Y.Object(M.core_availability.plugin);

M.availability_skillpoints.form.initInner = function(skills) {
    this.skills = skills;
};

M.availability_skillpoints.form.getNode = function(json) {
    // Create HTML structure.
    var html = '<span class="col-form-label pr-3"> ' + M.util.get_string('title', 'availability_skillpoints') + '</span>' +
        ' <span class="availability-group form-group"><label>' +
        '<span class="accesshide">' + M.util.get_string('label_skill', 'availability_skillpoints') + ' </span>' +
        '<select class="custom-select" name="skill" title="' + M.util.get_string('label_skill', 'availability_skillpoints') + '">' +
        '<option value="0">' + M.util.get_string('choosedots', 'moodle') + '</option>';

    for (var i = 0; i < this.skills.length; i++) {
        var skill = this.skills[i];
        // String has already been escaped using format_string.
        html += '<option value="' + skill.id + '">' + skill.name + '</option>';
    }

    html += '</select></label> <label><span class="accesshide">' +
        M.util.get_string('label_points', 'availability_skillpoints') +
        ' </span><input class="form-control" type="number" name="e" min="1" step="1"></label></span>';

    var node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    // Set initial values.
    if (json.skill !== undefined &&
        node.one('select[name=skill] > option[value=' + json.skill + ']')) {
        node.one('select[name=skill]').set('value', '' + json.skill);
    }

    if (json.e !== undefined) {
        node.one('input[name=e]').set('value', json.e);
    }

    // Add event handlers (first time only).
    if (!M.availability_skillpoints.form.addedEvents) {
        M.availability_skillpoints.form.addedEvents = true;
        var root = Y.one('.availability-field');
        root.delegate('change', function() {
            // Whichever dropdown changed, just update the form.
            M.core_availability.form.update();
        }, '.availability_skillpoints select');
        root.delegate('change', function() {
            // Whichever dropdown changed, just update the form.
            M.core_availability.form.update();
        }, '.availability_skillpoints input[name=e]');
    }

    return node;
};

M.availability_skillpoints.form.fillValue = function(value, node) {
    value.skill = parseInt(node.one('select[name=skill]').get('value'), 10);
    value.e = parseInt(node.one('input[name=e]').get('value'));
};

M.availability_skillpoints.form.fillErrors = function(errors, node) {
    var skillid = parseInt(node.one('select[name=skill]').get('value'), 10);
    if (skillid === 0) {
        errors.push('availability_skillpoints:validskill');
    }
    var e = parseInt(node.one('input[name=e]').get('value'), 10);

    if (e === undefined || e === '' || e <= 0) {
        errors.push('availability_skillpoints:validnumber');
    }
};

/* global Helpers, MultiContactForm */

MultiContactForm = {

    forms: [],
    validator: {
        'name[]': {
            validators: {
                notEmpty: {
                    message: "Name is required"
                },
                stringLength: {
                    min: 5,
                    max: 100,
                    message: 'The name must have from 5 to 100 char size'
                },
                regexp: {
                    regexp: /^[A-Za-z\ ]+$/,
                    message: 'The name can only consist of alphabetical and white spaces'
                }
            }
        },
        'email[]': {
            validators: {
                notEmpty: {
                    message: "Email is required"
                },
                emailAddress: {
                    message: 'The value is not a valid email address'
                },
                stringLength: {
                    min: 5,
                    max: 100,
                    message: 'The email must have from 5 to 100 char size'
                }
            }
        },
        'phone_number[]': {
            validators: {
                notEmpty: {
                    message: "The phone number is required"
                },
                stringLength: {
                    min: 5,
                    max: 100,
                    message: 'The phone number must have from 5 to 100 char size'
                },
                regexp: {
                    regexp: /^[0-9]+$/,
                    message: 'The phone number can only consist of numbers'
                }
            }
        }
    },
    init: function () {
        $('#btn-add').click(function (ev) {
            ev.preventDefault();
            MultiContactForm.add();
        });
        $('#btn-validate').click(function (ev) {
            ev.preventDefault();
            MultiContactForm.validate();
        });
        $('#btn-save').click(function (ev) {
            ev.preventDefault();
            MultiContactForm.save();
        });
        MultiContactForm.add();
    },
    add: function () {
        var contactForm = new ContactForm();
        var form = $('#form-template').clone();
        form.removeAttr('id');
        form.attr('data-id', contactForm.id);
        contactForm.ele = form;
        MultiContactForm.forms[contactForm.id] = contactForm;
        form.appendTo('#forms-container');
        Helpers.validator('div[data-id="' + contactForm.id + '"] form', MultiContactForm.validator);
    },
    validate: function () {
        $('#forms-container form').bootstrapValidator('validate');
    },
    save: function () {
        
    }

};

function ContactForm() {

    var self = this;
    this.id = null;
    this.ele = null;

    __construct = function () {
        self.id = Helpers.createUUID();
    };

    __construct();

}
;

$(MultiContactForm.init);
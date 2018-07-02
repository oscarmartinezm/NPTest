/* global Helpers, MultiContactForm */

MultiContactForm = {

    instances: [],
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
        contactForm.add();
        MultiContactForm.instances[contactForm.id] = contactForm;
    },
    save: function () {
        if(MultiContactForm.validate()){
            $('#main-form')[0].submit();
        } else {
            $('#validation-errors-modal').modal('show');
        }
    },
    validate: function () {
        var result = $('#main-form').bootstrapValidator('validate').has('.has-error').length;
        return (result > 0 ? false : true);
    },
    destroyValidation: function(){
        $('#main-form').bootstrapValidator('destroy');
    },
    initValidation: function(){
        Helpers.validator('#main-form', MultiContactForm.validator);
    }
};

function ContactForm() {

    var self = this;
    this.id = null;

    __construct = function () {
        self.id = Helpers.createUUID();
    };

    this.add = function () {
        var form = $('#form-template').clone();
        form.removeAttr('id');
        form.attr('data-formid', self.id);
        $('.btn-delete', form).click(function(ev){
            ev.preventDefault();
            self.remove();
        });
        form.appendTo('#forms-container');
        checkDeleteButtons();
        MultiContactForm.destroyValidation();
        MultiContactForm.initValidation();
    };

    this.remove = function () {
        MultiContactForm.destroyValidation();
        $('div[data-formid="' + self.id + '"]').remove();
        checkDeleteButtons();
        MultiContactForm.initValidation();
    };
    
    checkDeleteButtons = function(){
        var deleteBtns = $('#main-form .btn-delete');
        //console.log(deleteBtns.get(0));
        if(deleteBtns.length < 2){
            deleteBtns.addClass('delete-button-hidden');
        } else {
            deleteBtns.removeClass('delete-button-hidden');
        }
    };

    __construct();

}

$(MultiContactForm.init);
/**
 * @copyright Copyright (c) 2018 Kevin Polez <kevin@hypatie.xyz>
 *
 * @author Kevin Polez <kevin@hypatie.xyz>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

(function (OC, window, $, undefined) {
'use strict';

$(document).ready(function () {

/**
 * Helper for get the first letter of a string
 */
Handlebars.registerHelper('firstLetter', function( context, options) {
    return context.substring(0, 1);
});

/**
 * Helper for force number display with two decimal
 */
Handlebars.registerHelper('toFixed', function(number) {
    return parseFloat(number).toFixed(2);
});

var translations = {
    newReckoning: $('#new-reckoning-string').text()
};

// this reckonings object holds all our reckonings
var Reckonings = function (baseUrl) {
    this._baseUrl = baseUrl;
    this._reckonings = [];
    this._activeReckoning = undefined;
};

Reckonings.prototype = {

    // find a reckoning from his ID and set it active
    load: function (id) {
        var self = this;
        console.log("load reckoning");
        this._reckonings.forEach(function (reckoning) {
            if (reckoning.id === id) {
                reckoning.active = true;
                self._activeReckoning = reckoning;
                self.compute();
            } else {
                reckoning.active = false;
            }
        });
    },

    // round function for number
    round: function(value) {
       value = parseFloat(value);
       value = +(Math.ceil(value + "e+2") +"e-2");
       return value;
    },

    // find all lines related to a participant
    findTotalByParticipant: function(participant) {
        var self = this;
        var total = 0;
        this._activeReckoning.lines.forEach(function(line) {
            if ( line.who == participant.name) {
                total += parseFloat(line.amount);
            }
        });
        return self.round(total);
    },

    // compute Total, Solde and Balance
    compute: function() {
        var self = this;
        console.log("compute");
        var reckoning = self._activeReckoning;
        self._activeReckoning.total = 0;
        self._activeReckoning.balance = [];

        // total spent by participants
        console.log(this._activeReckoning.participants);
        this._activeReckoning.participants.forEach(function(participant) {
            var total = self.findTotalByParticipant(participant);
            var index = self._activeReckoning.participants.indexOf(participant);
            self._activeReckoning.participants[index].total = total;
            self._activeReckoning.total += total;
        });

       // solde compute
       var totalByParticipant = self._activeReckoning.total / self._activeReckoning.participants.length
       totalByParticipant = self.round(totalByParticipant);
       this._activeReckoning.participants.forEach(function(participant) {
           var index = self._activeReckoning.participants.indexOf(participant);
           self._activeReckoning.participants[index].solde = self.round(participant.total - totalByParticipant);
       });

       // sort participants by solde
       this._activeReckoning.participants.sort(function(a, b) {
         if ( a.solde < b.solde) return -1;
         else if ( a.solde > b.solde) return 1;
         return 0;
       });

       // balance compute
       this._activeReckoning.participants.forEach(function(participant) {
           var index = self._activeReckoning.participants.indexOf(participant);
           var futurSolde = participant.solde;

            while (futurSolde < 0 ) {
             // find a participant with a positive solde (handle previous balance line)
             var participantPositive = self._activeReckoning.participants.find(function(element) {
               var solde = element.solde;
               self._activeReckoning.balance.forEach(function(line) {
                 if (line.credit == element.name) solde -= line.amount;
               });
               return solde > 0;
             });

             // create a balance line
             if ( participantPositive.solde - Math.abs(futurSolde) >= 0 )
             {
               self._activeReckoning.balance.push({
                 'debit': participant.name,
                 'credit': participantPositive.name,
                 'amount': Math.abs(futurSolde)
               });
               futurSolde += Math.abs(futurSolde);
             }
             else {
               self._activeReckoning.balance.push({
                 'debit': participant.name,
                 'credit': participantPositive.name,
                 'amount': participantPositive.solde
               });
               futurSolde += participantPositive.solde;
             }

           }
       });
     },

     // Return the active reckoning
     getActive: function () {
         return this._activeReckoning;
     },

     // Delete the active reckoning
     // DELETE /reckoning/{id}
     removeActive: function () {
         var index;
         var deferred = $.Deferred();
         var id = this._activeReckoning.id;
         this._reckonings.forEach(function (reckoning, counter) {
             if (reckoning.id === id) {
                 index = counter;
             }
         });

         if (index !== undefined) {
             // delete cached active reckoning if necessary
             if (this._activeReckoning === this._reckonings[index]) {
                 delete this._activeReckoning;
             }

             this._reckonings.splice(index, 1);

             $.ajax({
                 url: this._baseUrl + '/' + id,
                 method: 'DELETE'
             }).done(function () {
                 deferred.resolve();
             }).fail(function () {
                 deferred.reject();
             });
         } else {
             deferred.reject();
         }
         return deferred.promise();
     },

     // Create a reckoning
     // POST /reckoning
     create: function (reckoning) {
         var deferred = $.Deferred();
         var self = this;
         $.ajax({
             url: this._baseUrl,
             method: 'POST',
             contentType: 'application/json',
             data: JSON.stringify(reckoning)
         }).done(function (reckoning) {
             self._reckonings.push(reckoning);
             self._activeReckoning = reckoning;
             self.load(reckoning.id);
             deferred.resolve();
         }).fail(function () {
             deferred.reject();
         });
         return deferred.promise();
     },

     // return all reckonings
     getAll: function () {
         return this._reckonings;
     },

     // load all reckonings
     // GET /reckonings
     loadAll: function () {
         var deferred = $.Deferred();
         var self = this;
         $.get(this._baseUrl).done(function (reckonings) {
             self._activeReckoning = undefined;
             self._reckonings = reckonings;
             deferred.resolve();
         }).fail(function () {
             deferred.reject();
         });
         return deferred.promise();
     },

     // update active reckoning
     // PUT /reckonings/{id}
     updateActive: function (title, description) {
         var reckoning = this.getActive();
         reckoning.title = title;
         reckoning.description = description;

         return $.ajax({
             url: this._baseUrl + '/' + reckoning.id,
             method: 'PUT',
             contentType: 'application/json',
             data: JSON.stringify(reckoning)
         });
     },

     // Add a line on the active reckoning
     // POST /reckonings/{id}/lines
     addLine: function( amount, when, who, why) {
        var deferred = $.Deferred();
        var reckoning = this.getActive();
        var self = this;
        var line = {
          'reckoningId': reckoning.id,
          'amount': amount,
          'when': when,
          'who': who,
          'why': why
        };
        $.ajax({
            url: this._baseUrl + '/' + reckoning.id + '/lines',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(line)
        }).done(function(reckoning) {
          var index = self._reckonings.findIndex(function(element){
              return element.id == reckoning.id;
          });
          self._reckonings[index] = reckoning;
          self.load(reckoning.id);
          deferred.resolve();
        }).fail(function() {
          deferred.reject();
        });
        return deferred.promise();
     },

     // Update a line on the active reckoning
     // PUT /reckonings/{id}/lines/{lineId}
     updateLine: function(lineId, amount, when, who, why) {
       var deferred = $.Deferred();
       var reckoning = this.getActive();
       var self = this;

       var line = {
         'reckoningId': reckoning.id,
         'id': lineId,
         'amount': amount,
         'when': when,
         'who': who,
         'why': why
       };

       $.ajax({
           url: this._baseUrl + '/' + reckoning.id + '/lines/'+lineId,
           method: 'PUT',
           contentType: 'application/json',
           data: JSON.stringify(line)
       }).done(function(newReckoning) {
         var index = self._reckonings.findIndex(function(element){
             return element.id == reckoning.id;
         });
         self._reckonings[index] = newReckoning;
         self.load(newReckoning.id);
         deferred.resolve();
       }).fail(function() {
          deferred.reject();
       });
       return deferred.promise();
     },

     // Delete a line on the active reckoning
     // DELETE /reckonings/{id}/lines/{lineId}
     deleteLine: function(lineId) {
       var deferred = $.Deferred();
       var reckoning = this.getActive();
       var self = this;

       $.ajax({
           url: this._baseUrl + '/' + reckoning.id + '/lines/'+lineId,
           method: 'DELETE',
           contentType: 'application/json'
       }).done(function(newReckoning) {
          var index = self._reckonings.findIndex(function(element){
             return element.id == reckoning.id;
          });
          self._reckonings[index] = newReckoning;
          self.load(newReckoning.id);
          deferred.resolve();
       }).fail(function() {
          deferred.reject();
       });

       return deferred.promise();
    },

    // Add a participant on a reckoning
    // POST /reckonings/{id}/participants
    addParticipant: function(name, percent) {
        var deferred = $.Deferred();
        var reckoning = this.getActive();
        var self = this;
        var participant = {
            'reckoningId': reckoning.id,
            'name': name,
            'percent': percent
        };

        $.ajax({
            url: this._baseUrl + '/' + reckoning.id + '/participants',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(participant)
        }).done(function(newReckoning) {
            var index = self._reckonings.findIndex(function(element){
               return element.id == reckoning.id;
            });
            self._reckonings[index] = newReckoning;
            self.load(newReckoning.id);
            deferred.resolve();
        }).fail(function() {
            deferred.reject();
        });

        return deferred.promise();
    },

    // Update a participant on a reckoning
    // PUT /reckonings/{id}/participants/{participantId}
    updateParticipant: function(participantId, name, percent) {
        var deferred = $.Deferred();
        var reckoning = this.getActive();
        var self = this;

        var participant = {
          'reckoningId': reckoning.id,
          'id': participantId,
          'name': name,
          'percent': percent
        };

        $.ajax({
            url: this._baseUrl + '/' + reckoning.id + '/participants/'+participantId,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(participant)
        }).done(function(newReckoning) {
            var index = self._reckonings.findIndex(function(element){
               return element.id == reckoning.id;
            });
            self._reckonings[index] = newReckoning;
            self.load(newReckoning.id);
            deferred.resolve();
        }).fail(function() {
            deferred.reject();
        });

        return deferred.promise();
    },

    // Delete a participant on a reckoning
    // DELETE /reckonings/{id}/participants/{participantId}
    deleteParticipant: function(participantId) {
        console.log("delete participant");
        var deferred = $.Deferred();
        var reckoning = this.getActive();
        var self = this;

        $.ajax({
            url: this._baseUrl + '/' + reckoning.id + '/participants/'+participantId,
            method: 'DELETE',
            contentType: 'application/json'
        }).done(function(newReckoning) {
            console.log("ajax done");
            console.log(newReckoning);
            var index = self._reckonings.findIndex(function(element){
               return element.id == reckoning.id;
            });
            self._reckonings[index] = newReckoning;
            self.load(newReckoning.id);
            deferred.resolve();
        }).fail(function() {
            deferred.reject();
        });

        return deferred.promise();
    },

    // Get participant name list
    getParticipants: function() {
        var list = [];
        if ( this._activeReckoning !== undefined ) {
            this._activeReckoning.participants.forEach(function(p) {
                list.push(p.name);
            });
        }
        return list;
    }
 };

 // this will be the view that is used to update the html
 var View = function (reckonings) {
     this._reckonings = reckonings;
 };

 View.prototype = {

     /**
      * Render content template.
      * this template display all graphs :
      * - Expenses by users
      * - Solde by users
      * And compute balance.
      */
     renderContent: function () {
         var source = $('#content-tpl').html();
         var template = Handlebars.compile(source);
         var html = template({reckoning: this._reckonings.getActive()});

         $('#editor').html(html);

         $( ".tabs" ).tabs()

        var self = this;

        // create an array with all participants
        var reckoning = this._reckonings.getActive();
        if ( reckoning !== undefined ) {
          var participantArray = [];
          var amountArray = [];
          var soldeArray = [];
          reckoning.participants.forEach(function(participant) {
            participantArray.push(participant.name);
            amountArray.push(participant.total);
            soldeArray.push(participant.solde);
          });

          var chartExpenseByUser = Highcharts.chart('chartExpenseByUser', {
                  chart: {
                      type: 'bar'
                  },
                  title: {
                      text: ''
                  },
                  xAxis: {
                      categories: participantArray
                  },
                  yAxis: {
                      title: {
                          text: 'Euros'
                      }
                  },
                  series: [{
                      name: 'Amount',
                      data: amountArray
                  }]
              });
          var soldeByUser = Highcharts.chart('chartSoldeByUser', {
                  chart: {
                      type: 'bar'
                  },
                  title: {
                      text: ''
                  },
                  xAxis: {
                      categories: participantArray
                  },
                  yAxis: {
                      title: {
                          text: 'Euros'
                      }
                  },
                  plotOptions: {
                    series: {
                      className: 'main-color',
                      negativeColor: true
                    }
                  },
                  series: [{
                      name: 'Amount',
                      data: soldeArray
                  }]
              });
         }

     },

     // check amount
     checkAmount: function(element, amount) {
       if (/^(\-|\+)?([0-9]+(\.[0-9]+)?|Infinity)$/
          .test(amount)) {
           $(element).addClass('ok');
           $(element).removeClass('warning');
          return true;
       }
       $(element).addClass('warning');
       $(element).removeClass('ok');
       return false;
     },
     // check who
     checkWho: function(element, who) {
       if ( who === "" ) {
         $(element).addClass('warning');
         $(element).removeClass('ok');
         return false;
       }
       $(element).addClass('ok');
       $(element).removeClass('warning');
       return true;
     },
     // check why
     checkWhy: function(element, why) {
       if ( why === "" ) {
         $(element).addClass('warning');
         $(element).removeClass('ok');
         return false;
       }
       $(element).addClass('ok');
       $(element).removeClass('warning');
       return true;
     },
     // check when
     checkWhen: function(element, when) {
       if ( when === "" ) {
         $(element).addClass('warning');
         $(element).removeClass('ok');
         return false;
       }
       $(element).addClass('ok');
       $(element).removeClass('warning');
       return true;
     },

     renderList: function() {
       var source = $('#list-tpl').html();
       var template = Handlebars.compile(source);
       var html = template({reckoning: this._reckonings.getActive()});

       $('#list_editor').html(html);
       var self = this;

       // show add expense form when click on a.addExpense
       $('a.addExpense').on('click',function(event){
         event.preventDefault();
         $('.addExpenseForm').toggleClass('hidden');
       });

       // show participant form when click on a.formParticipant
       $('a.formParticipant').on('click',function(event){
         event.preventDefault();
         $('.participantForm').toggleClass('hidden');
       });

       // check if amount is correct
       $('#app-content input.combien').keydown(function(event) {
         self.checkAmount(this, this.value+event.key);
       });

       $('#app-content input.qui').keydown(function(event) {
         self.checkWho(this, this.value+event.key);
       });

       $('#app-content input.quoi').keydown(function(event) {
         self.checkWhy(this, this.value+event.key);
       });

       $('#app-content input.quand').keydown(function(event) {
         self.checkWhen(this, this.value+event.key);
       });

       // datepicker
       $('#app-content input.quand').datepicker({
         onSelect: function(dateText) {
           $('#app-content input.quand').addClass('ok');
           $('#app-content input.quand').removeClass('warning');
         }
       });

        // autocompletion
        $('#app-content input.qui').autocomplete({
            source : self._reckonings.getParticipants(),
            minLength: 0
        });

        // handle new participant
        $('.participantForm button.new_participant').click(function() {
            console.log("add");
            var form = $(this).parent('.addParticipantForm');
            var name = $('input.name', form).val();
            var percent = $('input.percent', form).val();
            self._reckonings.addParticipant(name, percent).done(function() {
                self.render();
            }).fail(function() {
                alert('Could not add participant on reckoning');
            });
        });

        // handle update participant
        $('.participantForm button.update_participant').click(function() {
            console.log("update");
            var form = $(this).parent('.updateParticipantForm');
            var participantId = $(form).data('id');
            var name = $('input.name', form).val();
            var percent = $('input.percent', form).val();
            self._reckonings.updateParticipant(participantId, name, percent).done(function() {
                self.render();
            }).fail(function() {
                alert('Could not update participant on reckoning');
            });
        });

        // handle delete participant
        $('.participantForm button.delete_participant').click(function() {
            console.log("delete");
            var form = $(this).parent('.updateParticipantForm');
            var participantId = $(form).data('id');
            console.log("before delete participant")
            self._reckonings.deleteParticipant(participantId).done(function() {
                console.log("before render")
                self.render();
            }).fail(function() {
                alert('Could not delete participant on reckoning');
            });
        });

       // handle new line
       $('.addExpenseForm button.new_line').click(function() {
           // get the form
           var form = $(this).parent('.addExpenseForm');

           // get values
           var amount = $('input.combien', form).val();
           var when = $('input.quand', form).val();
           var who = $('input.qui', form).val();
           var why = $('input.quoi', form).val();

            // check values
            var resultAmount = self.checkAmount($('input.combien', form),amount);
            var resultWhen = self.checkWhen($('input.quand', form),when);
            var resultWho = self.checkWho($('input.qui', form),who);
            var resultWhy = self.checkWhy($('input.quoi', form),why);

            // if everythings are OK, add the new line
            // else, show a warning.
            if ( resultAmount == true
              && resultWhen == true
              && resultWho == true
              && resultWhy == true ) {
              self._reckonings.addLine(amount, when, who, why).done(function() {
                  self.render();
              }).fail(function() {
                alert('Could not add line on reckoning');
              });
            }
            else {
              $('.addExpenseForm p.message').text("Sorry, There are some misformated data on your request. You should fix that before send a new expense.");
              $('.addExpenseForm p.message').addClass('error');
            }
       });

       // handle update line
       $('.updateExpenseForm button.update_line').click(function() {
             // get the form
             var form = $(this).parent('.updateExpenseForm');

             // get values
             var amount = $('input.combien', form).val();
             var when = $('input.quand', form).val();
             var who = $('input.qui', form).val();
             var why = $('input.quoi', form).val();

             // check values
             var resultAmount = self.checkAmount($('input.combien', form),amount);
             var resultWhen = self.checkWhen($('input.quand', form),when);
             var resultWho = self.checkWho($('input.qui', form),who);
             var resultWhy = self.checkWhy($('input.quoi', form),why);

             var lineId = $(form).data('id');

             // if everythings are OK, update the new line
             // else, show a warning.
             if ( resultAmount == true
               && resultWhen == true
               && resultWho == true
               && resultWhy == true ) {
               self._reckonings.updateLine(lineId, amount, when, who, why).done(function() {
                   self.render();
               }).fail(function() {
                   alert('Could not add line on reckoning');
               });
             }
             else {
               $('.addExpenseForm p.message').text("Sorry, There are some misformated data on your request. You should fix that before send a new expense.");
               $('.addExpenseForm p.message').addClass('error');
             }
       });

       // handle cancel button
       $('.updateExpenseForm button.cancel_update').click(function() {
            $(this).parent('.updateExpenseForm').hide();
       });

       // popovermenu
       $('.app-content-list-item .icon-more').click(function() {
         var menu = $(this).siblings('.popovermenu');
         $('.popovermenu').not(menu).removeClass('open');
         $(menu).toggleClass('open');
       });

       // handle delete line
       $('#app-content .delete_line').click(function() {
         var id = parseInt($(this).parents('.app-content-list-item').data('id'), 10);
         self._reckonings.deleteLine(id).done(function() {
            self.render();
         }).fail(function() {
           alert('Could not delete line');
         });
       });

       // handle edit line
       $('#app-content .edit_line').click(function() {
         $(this).parents('.app-content-list-item').next('.updateExpenseForm').show();
         $(this).parents('.popovermenu').removeClass('open');
       });

     },
     renderNavigation: function () {
         var source = $('#navigation-tpl').html();
         var template = Handlebars.compile(source);
         var html = template({reckonings: this._reckonings.getAll()});

         $('#app-navigation ul').html(html);

         var self = this;

         // popovermenu
         $('.app-navigation-entry-utils-menu-button button').click(function() {
           var menu = $(this).parents('.app-navigation-entry-utils').next('.app-navigation-entry-menu');
           $('.app-navigation-entry-menu').not(menu).removeClass('open');
           $(menu).toggleClass('open');
         });


         // handle create a new reckoning
         $('#new-reckoning').click(function () {
             var reckoning = {
                 title: translations.newReckoning,
                 content: ''
             };

             self._reckonings.create(reckoning).done(function() {
                 self.render();
                 $('#editor textarea').focus();
             }).fail(function () {
                 alert('Could not create reckoning');
             });
         });

         // handle delete a reckoning
         $('#app-navigation a.deleteReckoning').click(function () {
             var entry = $(this).closest('.reckoning');
             entry.find('.app-navigation-entry-menu').removeClass('open');

             var id = parseInt(entry.data('id'), 10);
             self._reckonings.load(id);

             self._reckonings.removeActive().done(function () {
                 self.render();
             }).fail(function () {
                 alert('Could not delete reckoning, not found');
             });
         });

         // handle edit a reckoning
         $('#app-navigation a.editReckoning').click(function() {
            var entry = $(this).closest('.reckoning');

            entry.find('.app-navigation-entry-menu').removeClass('open');
            var id = parseInt(entry.data('id'), 10);

            self._reckonings.load(id);
            var reckoning = self._reckonings.getActive()

            var form = entry.find('.updateReckoningForm')
            $('.updateReckoningForm').not(form).hide();
            $(form).show();
            self.renderContent();
            self.renderList();
            $('input.title',form).val(reckoning.title);
            $('textarea.description',form).val(reckoning.description);
         });

         // handle cancel edit
         $('.updateReckoningForm button.cancel').click(function() {
              $(this).closest('.updateReckoningForm').hide();
         });

         // handle save
         $('.updateReckoningForm button.save').click(function() {
            var form = $(this).closest('.updateReckoningForm');
            var title = $('input.title',form).val();
            var description = $('textarea.description',form).val();
            self._reckonings.updateActive(title, description);
            $(form).hide();
            self.render();
         });

         // load a reckoning
         $('#app-navigation .reckoning > a').click(function () {
             var id = parseInt($(this).parent().data('id'), 10);
             self._reckonings.load(id);
             self.render();
         });
     },

    // render the templates
    render: function () {
        this.renderNavigation(); // navigation template
        this.renderList(); // list template
        this.renderContent(); // content template
    }
};

 var reckonings = new Reckonings(OC.generateUrl('/apps/sharedexpenses/reckonings'));
 var view = new View(reckonings);
 reckonings.loadAll().done(function () {
     view.render();
 }).fail(function () {
     alert('Could not load reckonings');
 });


 });

 })(OC, window, jQuery);

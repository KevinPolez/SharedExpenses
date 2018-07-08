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

 Handlebars.registerHelper('firstLetter', function( context, options) {
   return context.substring(0, 1);
 });

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
     load: function (id) {
         var self = this;
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

     round: function(value) {
       value = parseFloat(value);
       value = +(Math.ceil(value + "e+2") +"e-2");
       return value;
     },
     compute: function() {
       var self = this;
       var reckoning = self._activeReckoning;
       self._activeReckoning.total = 0;
       self._activeReckoning.participants = [];
       self._activeReckoning.balance = [];

       // total spent compute
       this._activeReckoning.lines.forEach(function(line) {
         // find if participant already exist
         var participant = self._activeReckoning.participants.find(function(element) {
           return element.name === line.who;
         });
         // if new
         if ( participant === undefined ) {
            self._activeReckoning.participants.push({
              'name': line.who,
              'total': self.round(line.amount)
            });
         } else { // if already exist
           participant.total += self.round(line.amount);
           var index = self._activeReckoning.participants.indexOf(participant);
           self._activeReckoning.participants[index] = participant;
         }
         self._activeReckoning.total += self.round(line.amount);
       });

       // solde compute
       var totalByParticipant = self._activeReckoning.total / self._activeReckoning.participants.length
       totalByParticipant = self.round(totalByParticipant);
       this._activeReckoning.participants.forEach(function(participant) {
           var index = self._activeReckoning.participants.indexOf(participant);
           participant.solde = participant.total - totalByParticipant;
           participant.solde = self.round(participant.solde);
           self._activeReckoning.participants[index] = participant;
       });

       // sort participants array by solde
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

     getActive: function () {
         return this._activeReckoning;
     },
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
     getAll: function () {
         return this._reckonings;
     },
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
        }).done(function(line) {
          self._activeReckoning.lines.push(line);
          self.load(reckoning.id);
          deferred.resolve();
        }).fail(function() {
          deferred.reject();
        });
        return deferred.promise();
     },
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
       }).done(function(line) {
         var index = self._activeReckoning.lines.findIndex(function(element){
           return element.id == line.id;
         });
         self._activeReckoning.lines[index] = line;
         self.load(reckoning.id);
         deferred.resolve();
       }).fail(function() {
          deferred.reject();
       });
       return deferred.promise();
     },
     deleteLine: function(lineId) {
       var deferred = $.Deferred();
       var reckoning = this.getActive();
       var self = this;

       $.ajax({
           url: this._baseUrl + '/' + reckoning.id + '/lines/'+lineId,
           method: 'DELETE',
           contentType: 'application/json'
       }).done(function(line) {
         var index;
         self._activeReckoning.lines.forEach(function (l, counter) {
             if (l.id === line.id) {
                 index = counter;
             }
         });
         self._activeReckoning.lines.splice(index,1);
         self.load(reckoning.id);
         deferred.resolve();
       }).fail(function() {
         deferred.reject();
       });
       return deferred.promise();
     }
 };

 // this will be the view that is used to update the html
 var View = function (reckonings) {
     this._reckonings = reckonings;
 };

 View.prototype = {
     renderContent: function () {
         var source = $('#content-tpl').html();
         var template = Handlebars.compile(source);
         var html = template({reckoning: this._reckonings.getActive()});

         $('#editor').html(html);

        var self = this;
         // handle save reckoning
         $('#app-content button.save').click(function () {
             var description = $('#app-content textarea').val();
             var title = $('#app-content input.title').val();

             self._reckonings.updateActive(title, description).done(function () {
                 self.render();
             }).fail(function () {
                 alert('Could not update reckoning, not found');
             });
         });

         // handle delete reckoning
         $('#app-content button.delete').click(function () {
             self._reckonings.removeActive().done(function () {
                 self.render();
             }).fail(function () {
                 alert('Could not update reckoning, not found');
             });
         });
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
                     text: 'Expenses by user'
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
                     text: 'Solde by user'
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

       // show add expense form when click on a.addExpense
       $('a.addExpense').on('click',function(event){
         event.preventDefault();
         $('.addExpenseForm').toggleClass('hidden');
       });

       // show reckoning resume when click on a.resume
       $('a.resume').on('click',function(event){
         event.preventDefault();
         $('.resumeReckoning').toggleClass('hidden');
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

         // create a new reckoning
         var self = this;
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

         // delete a reckoning
         $('#app-navigation .reckoning .delete').click(function () {
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

         // load a reckoning
         $('#app-navigation .reckoning > a').click(function () {
             var id = parseInt($(this).parent().data('id'), 10);
             self._reckonings.load(id);
             self.render();
             $('#editor textarea').focus();
         });
     },
     render: function () {
         this.renderNavigation();
         this.renderContent();
         this.renderList();
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

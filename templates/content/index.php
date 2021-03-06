<script id="content-tpl" type="text/x-handlebars-template">
    {{#if reckoning}}
      <div class="total">
        <h2><?php p($l->t('Total'));?> : <b>{{ toFixed reckoning.total }} euros</b></h2>
      </div>

      <h2><?php p($l->t('Expense')); ?></h2>
      <div class="tabs">
          <ul>
              <li><a href="#tabsExpense-1">Graph</a></li>
              <li><a href="#tabsExpense-2">Data</a></li>
          </ul>
          <div id="tabsExpense-1">
              <div id="chartExpenseByUser" style="width:100%; height:400px;"></div>
          </div>
          <div id="tabsExpense-2">
              <ul>
              {{#each reckoning.participants }}
                  <li><b>{{name}}</b>&nbsp;<?php p($l->t('has spent')); ?>&nbsp;<b>{{toFixed total}} euros</b></li>
              {{/each}}
              </ul>
          </div>
      </div>

      <h2><?php p($l->t('Solde')); ?></h2>
      <div class="tabs">
          <ul>
              <li><a href="#tabsSolde-1">Graph</a></li>
              <li><a href="#tabsSolde-2">Data</a></li>
          </ul>
          <div id="tabsSolde-1">
              <div id="chartSoldeByUser" style="width:100%; height:400px;"></div>
          </div>
          <div id="tabsSolde-2">
              <ul>
              {{#each reckoning.participants }}
                  <li><b>{{name}}</b>&nbsp;:&nbsp;<b>{{toFixed solde}} euros</b></li>
              {{/each}}
              </ul>
          </div>
      </div>

      <h2><?php p($l->t('Balance')); ?></h2>
      <ul>
      {{#each reckoning.balance }}
          <li><b>{{debit}}</b>&nbsp;doit&nbsp;<b>{{toFixed amount}} euros</b> à <b>{{credit}}</b></li>
      {{/each}}
      </ul>
    {{/if}}
</script>

<script id="list-tpl" type="text/x-handlebars-template">
  {{#if reckoning}}

      <div>
          <a href="#" class="addExpense app-content-list-item">
            <div class="app-content-list-item-icon" style="background-color: rgb(0, 0, 0);">+</div>
            <div class="app-content-list-item-line-one"><?php p($l->t('Add an expense'));?></div>
          </a>
          <div class="addExpenseForm hidden">
              <div>
                <input class="qui" placeholder="<?php p($l->t('Who ?')); ?>"></input>
                <input class="quand" placeholder="<?php p($l->t('When ?')); ?>"></input>
                <input class="quoi" placeholder="<?php p($l->t('What ?')); ?>"></input>
                <input class="combien" placeholder="<?php p($l->t('How much ?')); ?>"></input>
              </div>
              <div>
                <h4>For ?</h4>
                {{#each reckoning.participants}}
                    <input id="for{{id}}" class="checkbox checkbox-white for" type="checkbox" value="{{name}}" checked="checked">
                    <label for="for{{id}}">{{name}}</label><br />
                {{/each}}
              </div>

              <div style="float:right">
                  <button class="new_line"><?php p($l->t('Add')); ?></button>
              </div>

              <br style="clear: both" />

              <p class="message"></p>
          </div>
      </div>
      <div>
          <a href="#" class="formParticipant app-content-list-item">
            <div class="app-content-list-item-icon" style="background-color: rgb(0, 0, 0);">+</div>
            <div class="app-content-list-item-line-one"><?php p($l->t('Participants'));?></div>
          </a>
          <div class="participantForm hidden">
              <div class="addParticipantForm">
                  <input class="name" placeholder="<?php p($l->t('Name ?')); ?>"></input>
                  <input class="percent" placeholder="<?php p($l->t('Percent ?')); ?>"></input>
                  <button class="new_participant"><?php p($l->t('Add')); ?></button>
              </div>

              <ul>
              {{#each reckoning.participants}}
                <li class="updateParticipantForm" data-id="{{id}}">
                  <input class="name" value="{{name}}"></input>
                  <input class="percent" value="{{percent}}"></input>
                  <button class="update_participant">Update</button>
                  <button class="delete_participant">Delete</button>
                </li>
              {{/each}}
              </ul>

              <p class="message"></p>
          </div>
      </div>
      {{#each reckoning.lines }}
          <div class="app-content-list-item" data-id="{{ id }}">
            <div class="app-content-list-item-icon" style="background-color: rgb(152, 59, 144);">{{firstLetter who}}</div>
            <div class="app-content-list-item-line-one">{{why}}</div>
            <div class="app-content-list-item-line-two"><b>{{who}}</b> <?php p($l->t('has paid'));?> <b>{{toFixed amount}} euros</b>&nbsp;for : {{#each for}}<span class="user">{{this}}</span>{{/each}}</div>
            <span class="app-content-list-item-details">{{when}}</span>
            <div class="app-content-list-item-menu">
              <div class="icon-more"></div>
              <div class="popovermenu">
                <ul>
                  <li>
                    <a href="#" class="icon-edit edit_line">
                        <span>Edit</span>
                    </a>
                  </li>
                  <li>
                    <a href="#" class="icon-delete delete_line">
                        <span>Delete</span>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="updateExpenseForm hidden" data-id="{{ id }}">
            <div>
                <input class="qui" value="{{who}}"></input>
                <input class="quand" value="{{when}}"></input>
                <input class="quoi" value="{{why}}"></input>
                <input class="combien" value="{{amount}}"></input>
            </div>

            <div>
              <h4>For ?</h4>
              {{#each ../reckoning.participants}}
                  <input id="forUpdate{{../id}}_{{id}}" class="checkbox checkbox-white for" type="checkbox" value="{{name}}" {{#ifIn name ../for }}checked="checked"{{/ifIn}}>
                  <label for="forUpdate{{../id}}_{{id}}">{{name}}</label><br />
              {{/each}}
            </div>

            <div style="float:right">
                <button class="update_line"><?php p($l->t('Update')); ?></button>
                <button class="cancel_update"><?php p($l->t('Cancel')); ?></button>
            </div>

            <br style="clear: both" />

            <p class="message"></p>
          </div>
      {{else}}
            <?php p($l->t('No reckoning'));?>
            <?php p($l->t('Add a new expense for start your reckoning'));?>
      {{/each}}
  {{else}}
      Shared Expenses est une application qui vous permet de gérer en toute simplicité des dépenses partagées entre ami.e.s.
      Choisissez un compte déjà existant ou créez en un nouveau.
  {{/if}}
</script>


<div class="app-content-list">
    <div id="list_editor"></div>
</div>

<div class="app-content-detail">
    <div id="editor"></div>
</div>

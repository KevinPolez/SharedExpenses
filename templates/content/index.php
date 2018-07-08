<script id="content-tpl" type="text/x-handlebars-template">
    {{#if reckoning}}
        <div class="metainfo">
            <h2>Général</h2>
            <div class="input">
              <label>Title</label>
              <input class="title" value="{{ reckoning.title }}"></input>
            </div>
            <div class="input">
              <label>Description</label>
              <textarea>{{ reckoning.description }}</textarea>
            </div>
            <div class="action">
              <button class="save"><?php p($l->t('Save')); ?></button>
              <button class="delete"><?php p($l->t('Delete')); ?></button>
            </div>
        </div>
        <h2>Options de partage</h2>
          <ul>
              <li>Mot de passe</li>
              <li>Lien</li>
          </ul>
    {{/if}}
</script>

<script id="list-tpl" type="text/x-handlebars-template">
  {{#if reckoning}}
      <div class="total">
        <h2><?php p($l->t('Total'));?> : <b>{{ toFixed reckoning.total }} euros</b></h2>
      </div>
      <div>
          <a href="#" class="addExpense app-content-list-item">
            <div class="app-content-list-item-icon" style="background-color: rgb(0, 0, 0);">+</div>
            <div class="app-content-list-item-line-one"><?php p($l->t('Add an expense'));?></div>
          </a>
          <div class="addExpenseForm hidden">
              <input class="qui" placeholder="<?php p($l->t('Who ?')); ?>"></input>

              <input class="quand" placeholder="<?php p($l->t('When ?')); ?>"></input>

              <input class="quoi" placeholder="<?php p($l->t('What ?')); ?>"></input>

              <input class="combien" placeholder="<?php p($l->t('How much ?')); ?>"></input>

              <button class="new_line"><?php p($l->t('Add')); ?></button>

              <p class="message"></p>
          </div>
          <a href="#" class="resume app-content-list-item">
            <div class="app-content-list-item-icon" style="background-color: rgb(0, 0, 0);">...</div>
            <div class="app-content-list-item-line-one"><?php p($l->t('Resume'));?></div>
          </a>
          <div class="resumeReckoning hidden">
            <div id="chartExpenseByUser" style="width:100%; height:400px;"></div>
            <div id="chartSoldeByUser" style="width:100%; height:400px;"></div>
            <ul>
            {{#each reckoning.participants }}
                <li><b>{{name}}</b>&nbsp;<?php p($l->t('has spent')); ?>&nbsp;<b>{{toFixed total}} euros</b></li>
            {{/each}}
            </ul>
            <h2><?php p($l->t('Solde')); ?></h2>
            <ul>
            {{#each reckoning.participants }}
                <li><b>{{name}}</b>&nbsp;:&nbsp;<b>{{toFixed solde}} euros</b></li>
            {{/each}}
            </ul>
            <h2><?php p($l->t('Balance')); ?></h2>
            <ul>
            {{#each reckoning.balance }}
                <li><b>{{debit}}</b>&nbsp;doit&nbsp;<b>{{toFixed amount}} euros</b> à <b>{{credit}}</b></li>
            {{/each}}
            </ul>
          </div>

      </div>
      {{#each reckoning.lines }}
          <div class="app-content-list-item" data-id="{{ id }}">
            <div class="app-content-list-item-icon" style="background-color: rgb(152, 59, 144);">{{firstLetter who}}</div>
            <div class="app-content-list-item-line-one">{{why}}</div>
            <div class="app-content-list-item-line-two"><b>{{who}}</b> <?php p($l->t('has paid'));?> <b>{{toFixed amount}} euros</b></div>
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
            <input class="qui" value="{{who}}"></input>

            <input class="quand" value="{{when}}"></input>

            <input class="quoi" value="{{why}}"></input>

            <input class="combien" value="{{amount}}"></input>

            <button class="update_line"><?php p($l->t('Update')); ?></button>
            <button class="cancel_update"><?php p($l->t('Cancel')); ?></button>

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

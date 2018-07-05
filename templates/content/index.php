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
        <h2><?php p($l->t('Total'));?> : <b>{{ reckoning.total }} euros</b></h2>
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
                <li><b>{{name}}</b>&nbsp;<?php p($l->t('has spent')); ?>&nbsp;<b>{{total}} euros</b></li>
            {{/each}}
            </ul>
            <h2><?php p($l->t('Solde')); ?></h2>
            <ul>
            {{#each reckoning.participants }}
                <li><b>{{name}}</b>&nbsp;:&nbsp;<b>{{solde}} euros</b></li>
            {{/each}}
            </ul>
            <h2><?php p($l->t('Balance')); ?></h2>
            <ul>
            {{#each reckoning.balance }}
                <li><b>{{debit}}</b>&nbsp;doit&nbsp;<b>{{amount}} euros</b> à <b>{{credit}}</b></li>
            {{/each}}
            </ul>
          </div>

      </div>
      {{#each reckoning.lines }}
          <a href="#" class="app-content-list-item">
            <div class="app-content-list-item-icon" style="background-color: rgb(152, 59, 144);">{{firstLetter who}}</div>
            <div class="app-content-list-item-line-one">{{why}}</diV>
            <div class="app-content-list-item-line-two"><b>{{who}}</b> <?php p($l->t('has paid'));?> <b>{{amount}} euros</b></div>
            <span class="app-content-list-item-details">{{when}}</span>
            <div class="icon-more"></div>
            <div class="popovermenu">
              <ul>
                <li>
                  <a href="#" class="icon-edit">
                      <span>Edit</span>
                  </a>
                  <a href="#" class="icon-delete">
                      <span>Delete</span>
                  </a>
                </li>
              </ul>
            </div>
          </a>
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

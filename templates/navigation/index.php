<!-- translation strings -->
<div style="display:none" id="new-reckoning-string">
  <?php p($l->t('New reckoning')); ?>
</div>

<script id="navigation-tpl" type="text/x-handlebars-template">
    {{#each reckonings}}
      <li class="reckoning {{#if active}}active{{/if}}" data-id="{{ id }}">
        <div class="app-navigation-entry-bullet"></div>
        <a href="#">{{ title }}</a>
        <div class="app-navigation-entry-utils">
            <ul>
                <li class="app-navigation-entry-utils-counter">{{lines.length}}</li>
                <li class="app-navigation-entry-utils-menu-button">
                  <button></button>
                </li>
            </ul>
        </div>
        <div class="app-navigation-entry-menu">
            <ul>
                <li>
                  <a href="#" class="editReckoning">
                    <span class="icon-rename"></span>
                    <span>Edit</span>
                  </a>
                </li>
                <li>
                  <a href="#" class="deleteReckoning">
                    <span class="icon-delete"></span>
                    <span>Delete</span>
                  </a>
                </li>
            </ul>
        </div>
        <div class="updateReckoningForm hidden">
          <div class="input">
            <label>Title</label>
            <input class="title"></input>
          </div>
          <div class="input">
            <label>Description</label>
            <textarea></textarea>
          </div>
          <div class="action">
            <button class="save"><?php p($l->t('Save')); ?></button>
            <button class="cancel"><?php p($l->t('Cancel')); ?></button>
          </div>
        </div>
      </li>
    {{/each}}
    <li id="new-reckoning">
      <a href="#" class="icon-add">
        <?php p($l->t('Add reckoning')); ?>
      </a>
    </li>
</script>

<ul></ul>

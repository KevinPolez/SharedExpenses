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
          </ul>
        </div
      </li>
    {{/each}}
    <li id="new-reckoning">
      <a href="#" class="icon-add">
        <?php p($l->t('Add reckoning')); ?>
      </a>
    </li>
</script>

<ul></ul>

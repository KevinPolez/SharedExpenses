<?php
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

script('sharedexpenses', 'script');
style('sharedexpenses', 'style');

$userId = $_['userId'];
$urlGenerator = $_['urlGenerator'];
$isUpdate = isset($_['reckoning']) && $_['reckoning'] !== null;

?>

<div id="app">
	<div id="app-content">
    <div id="app-content-wrapper">
      <div id="controls">
              <div id="breadcrump">
                  <div class	="crumb svg last" data-dir="/">
                    <a href="<?php p($urlGenerator->linkToRoute('sharedexpenses.page.index')); ?>">
                      <img class="svg" src="<?php print_unescaped(\OCP\Template::image_path('core', 'places/home.svg')); ?>" alt="Home">
                    </a>
                  </div>
              </div>
              <div class="crumb svg last">
						          <span>
						                  <?php if ($isUpdate): ?>
							                    <?php p($l->t('Edit reckoning') . ' ' . $reckoning->getTitle()); ?>
						                  <?php else: ?>
						                      <?php p($l->t('Create new reckoning')); ?>
						                  <?php endif; ?>
						          </span>
            </div>
      </div>
      <form name="finish_reckoning" action="<?php p($urlGenerator->linkToRoute('sharedexpenses.page.insert_reckoning')); ?>" method="POST">
        <input type="hidden" name="userId" id="userId" value="<?php p($userId); ?>" />
        <header class="row">
        </header>
        <div class="new_reckoning row">
            <div class="col-50">
              <label for="reckoningTitle" class="input_title"><?php p($l->t('Title')); ?></label>
						  <input type="text" class="input_field" id="reckoningTitle" name="reckoningTitle" value="<?php if (isset($title)) p($title); ?>" />
						  <label for="reckoningDesc" class="input_title"><?php p($l->t('Description')); ?></label>
              <textarea class="input_field" id="reckoningDesc" name="reckoningDesc"><?php if (isset($desc)) p($desc); ?></textarea>
            </div>
        </div>
        <div class="form-actions">
					<?php if ($isUpdate): ?>
						<input type="submit" id="submit_finish_reckoning" class="button btn primary" value="<?php p($l->t('Update reckoning')); ?>" />
					<?php else: ?>
						<input type="submit" id="submit_finish_reckoning" class="button btn primary" value="<?php p($l->t('Create reckoning')); ?>" />
					<?php endif; ?>
					<a href="<?php p($urlGenerator->linkToRoute('sharedexpenses.page.index')); ?>" id="submit_cancel_reckoning" class="button"><?php p($l->t('Cancel')); ?></a>
        </div>
      </form>
    </div>
  </div>
</div>

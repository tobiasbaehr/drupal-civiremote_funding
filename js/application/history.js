/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

'use strict';

(function (once) {
  Drupal.behaviors.civiremote_funding_application_history = {
    attach: function (context, settings) {
      once('civiremote_funding_application_history_filter', 'button[data-activity-filter]', context).forEach(
        function (button) {
          button.addEventListener('click', function() { toggleActivityVisibility(button); });
        }
      );

      function toggleActivityVisibility(button) {
        const kind = button.getAttribute('data-activity-filter');
        const hidden = button.classList.contains('active');
        if (hidden) {
          document.querySelectorAll('[data-activity-kind="' + kind + '"]')
            .forEach((activity) =>  { activity.style.display = 'block'; });
          button.classList.remove('active');
          button.classList.remove('button--primary');
        } else {
          document.querySelectorAll('[data-activity-kind="' + kind + '"]')
            .forEach((activity) => activity.style.display = 'none');
          button.classList.add('active');
          button.classList.add('button--primary');
        }
      }
    }
  };
})(once);

'use strict';

document.addEventListener('DOMContentLoaded', function () {

    // STOCK OUT: Filter works by selected villa
    const villaSelect = document.getElementById('villa_id');
    const workSelect = document.getElementById('work_id');

    if (villaSelect && workSelect) {

        function filterWorks() {
            const selectedVilla = villaSelect.value;

            Array.from(workSelect.options).forEach(function (option) {
                if (!option.value) {
                    return;
                }

                const matchesVilla =
                    option.dataset.villaId === selectedVilla;

                option.hidden = !matchesVilla;
                option.disabled = !matchesVilla;
            });

            const selectedOption = workSelect.selectedOptions[0];

            if (!selectedOption || selectedOption.disabled) {
                workSelect.value = '';
            }
        }

        villaSelect.addEventListener('change', function () {
            workSelect.value = '';
            filterWorks();
        });

        filterWorks();
    }


    // WORK CREATE / EDIT: Filter assignments by User or Team
    const typeSelect = document.getElementById('assignment_type');
    const idSelect = document.getElementById('assignment_id');

    if (typeSelect && idSelect) {

        function filterAssignments() {
            const selectedType = typeSelect.value;

            Array.from(idSelect.options).forEach(function (option) {
                if (!option.value) {
                    return;
                }

                const matchesType =
                    option.dataset.type === selectedType;

                option.hidden = !matchesType;
                option.disabled = !matchesType;
            });

            const selectedOption = idSelect.selectedOptions[0];

            if (selectedOption && selectedOption.disabled) {
                idSelect.value = '';
            }
        }

        typeSelect.addEventListener('change', filterAssignments);

        filterAssignments();
    }
});
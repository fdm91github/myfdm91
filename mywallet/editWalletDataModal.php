<div class="modal fade" id="editWalletDataModal" tabindex="-1" aria-labelledby="editWalletDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editWalletDataModalLabel">Modifica spesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editWalletDataStatus"></div>
                <form id="editWalletDataForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editWalletDataId">
                    <input type="hidden" name="wallet_id" id="editWalletDataWalletId">
                    <div class="form-group">
                        <label for="editWalletDataDescription">Descrizione</label>
                        <input type="text" name="description" id="editWalletDataDescription" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editWalletDataAmount">Costo</label>
                        <input type="number" name="amount" id="editWalletDataAmount" step="0.01" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editWalletDataBuyingDate">Data di acquisto</label>
                        <input type="date" name="buying_date" id="editWalletDataBuyingDate" class="form-control" required>
                    </div>

                    <div id="partsSection">
                        <h6>Prodotti</h6>
                        <div id="editPartsContainer"></div>
                        <button type="button" id="editAddPartBtn" class="btn btn-secondary w-100">Aggiungi Prodotto</button>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        let partsToDelete = [];

        // When the edit modal is shown, load the expense data and existing parts
        $('#editWalletDataModal').on('show.bs.modal', function (event) {
            partsToDelete = []; // Reset deletion tracker
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var description = button.data('description');
            var amount = button.data('amount');
            var buying_date = button.data('buying-date');
            var walletId = button.data('wallet-id');

            $('#editWalletDataId').val(id);
            $('#editWalletDataDescription').val(description);
            $('#editWalletDataAmount').val(amount);
            $('#editWalletDataBuyingDate').val(buying_date);
            $('#editWalletDataWalletId').val(walletId);

            // Load existing parts
            $('#editPartsContainer').empty();
            $.ajax({
                url: 'fetchParts.php',
                type: 'GET',
                data: { wallet_data_id: id },
                success: function(response) {
                    response.parts.forEach(function(part) {
                        addPartRow('editPartsContainer', part.part_name, part.part_cost, part.id);
                    });
                }
            });
        });

        // When the modal is hidden, reset the form and clear parts
        $('#editWalletDataModal').on('hidden.bs.modal', function () {
            $('#editWalletDataForm')[0].reset();
            $('#editPartsContainer').empty();
            $('#fileNameDisplay').val('');
            partsToDelete = [];
        });

        // Function to dynamically add a new part row
        function addPartRow(containerId, partName = '', partCost = '', partId = '') {
            var partRow = `
                <div class="form-row align-items-end mb-2" data-part-id="${partId}">
                    <input type="hidden" name="part_id[]" value="${partId}">
                    <div class="col">
                        <input type="text" name="part_name[]" class="form-control" placeholder="Nome prodotto" value="${partName}" required>
                    </div>
                    <div class="col">
                        <input type="number" name="part_cost[]" class="form-control" placeholder="Costo" value="${partCost}" step="0.01" required>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-danger removePartBtn">&times;</button>
                    </div>
                </div>`;
            $('#' + containerId).append(partRow);
        }

        // Add new part row when the "Aggiungi Prodotto" button is clicked
        $('#editAddPartBtn').click(function() {
            addPartRow('editPartsContainer');
        });

        // Handle removal of parts and track them for deletion if needed
        $(document).on('click', '.removePartBtn', function() {
            var partRow = $(this).closest('.form-row');
            var partId = partRow.data('part-id');
            if (partId) {
                partsToDelete.push(partId);
            }
            partRow.remove();
        });

        // Submit the edit form with validation on the sum of parts cost
        $('#editWalletDataForm').submit(function(e) {
            e.preventDefault();
            $('button[type="submit"]').prop('disabled', true);

            // Validate that the sum of parts cost does not exceed the total expense cost
            var totalExpense = parseFloat($('#editWalletDataAmount').val()) || 0;
            var partsSum = 0;
            $('input[name="part_cost[]"]').each(function() {
                partsSum += parseFloat($(this).val()) || 0;
            });

            if (partsSum > totalExpense) {
                $('#editWalletDataStatus').html('<div class="alert alert-danger">La somma dei costi dei prodotti non può superare il costo totale della spesa.</div>');
                $('button[type="submit"]').prop('disabled', false);
                return;
            }

            // Append partsToDelete to the form data
            $('<input>').attr({
                type: 'hidden',
                name: 'parts_to_delete',
                value: partsToDelete.join(',')
            }).appendTo('#editWalletDataForm');

            var formData = new FormData(this);

            $.ajax({
                url: 'editWalletData.php?id=' + $('#editWalletDataId').val(),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#editWalletDataStatus').html('<div class="alert alert-success">Spesa aggiornata con successo!</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#editWalletDataStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                    $('button[type="submit"]').prop('disabled', false);
                }
            });
        });
    });
</script>

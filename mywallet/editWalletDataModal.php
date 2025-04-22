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

                    <div class="form-group">
                        <label for="editWalletDataWalletId">Portafoglio</label>
                        <select name="wallet_id" id="editWalletDataWalletId" class="form-control" required>
                            <option value="">Seleziona portafoglio...</option>
                        </select>
                    </div>

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

        function loadWallets(selectedId) {
            $.ajax({
                url: 'fetchWallets.php',
                type: 'GET',
                success: function(response) {
                    var $select = $('#editWalletDataWalletId').empty().append('<option value="">Seleziona portafoglio...</option>');
                    response.wallets.forEach(function(w) {
                        var sel = (w.id == selectedId) ? ' selected' : '';
                        $select.append(`<option value="${w.id}"${sel}>${w.name}</option>`);
                    });
                }
            });
        }

        $('#editWalletDataModal').on('show.bs.modal', function(event) {
			partsToDelete = [];
			const button = $(event.relatedTarget);
			const id = button.data('id');

			// Chiamata unica per tutto
			$.ajax({
			  url: 'retrieveData.php',
			  type: 'GET',
			  dataType: 'json',
			  data: { id: id },
			  success: function(resp) {
				// 1) Popolo i campi base
				$('#editWalletDataId').val(resp.wallet_data.id);
				$('#editWalletDataDescription').val(resp.wallet_data.description);
				$('#editWalletDataAmount').val(resp.wallet_data.amount);
				$('#editWalletDataBuyingDate').val(resp.wallet_data.buying_date);

				// 2) Popolo il dropdown portafogli
				const $sel = $('#editWalletDataWalletId')
								.empty()
								.append('<option value=\"\">Seleziona portafoglio...</option>');
				resp.wallets.forEach(w => {
				  const sel = (w.id == resp.wallet_data.wallet_id) ? ' selected' : '';
				  $sel.append(`<option value=\"${w.id}\"${sel}>${w.name}</option>`);
				});

				// 3) Popolo le parti
				$('#editPartsContainer').empty();
				resp.parts.forEach(function(p) {
				  addPartRow('editPartsContainer', p.part_name, p.part_cost, p.id);
				});
			  }
			});
		});


        $('#editWalletDataModal').on('hidden.bs.modal', function () {
            $('#editWalletDataForm')[0].reset();
            $('#editPartsContainer').empty();
            partsToDelete = [];
        });

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

        $('#editAddPartBtn').click(function() {
            addPartRow('editPartsContainer');
        });

        $(document).on('click', '.removePartBtn', function() {
            var partRow = $(this).closest('.form-row');
            var partId = partRow.data('part-id');
            if (partId) partsToDelete.push(partId);
            partRow.remove();
        });

        $('#editWalletDataForm').submit(function(e) {
            e.preventDefault();
            $('button[type="submit"]').prop('disabled', true);

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

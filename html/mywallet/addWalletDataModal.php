<!-- Modale per aggiungere una nuova spesa -->
<div class="modal fade" id="addWalletDataModal" tabindex="-1" aria-labelledby="addWalletDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="addWalletDataStatus"></div>
                <form id="addWalletDataForm" enctype="multipart/form-data">
                    <input type="hidden" name="wallet_id" id="addWalletDataWalletId">
                    <div class="form-group">
                        <label for="addWalletDataDescription">Descrizione</label>
                        <input type="text" name="description" id="addWalletDataDescription" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="addWalletDataAmount">Costo</label>
                        <input type="number" name="amount" id="addWalletDataAmount" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="addWalletDataBuyingDate">Data di acquisto</label>
                        <input type="date" name="buying_date" id="addWalletDataBuyingDate" class="form-control" required>
                    </div>
                    
                    <!-- Parts section -->
                    <div id="partsSection">
                        <h6>Prodotti</h6>
                        <div id="addPartsContainer">
                        </div>
                        <button type="button" id="addNewPartBtn" class="btn btn-secondary w-100">Aggiungi voce</button>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-3">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
	$(document).ready(function() {
		$('#addWalletDataModal').on('show.bs.modal', function(event) {
			var button = $(event.relatedTarget);
			var walletId = button.data('wallet-id');
			var modal = $(this);
			modal.find('#addWalletDataWalletId').val(walletId);
		});

		$('#addNewPartBtn').click(function() {
			addPartRow('addPartsContainer');
		});

		function addPartRow(containerId, partName = '', partCost = '') {
			var partRow = `
				<div class="form-row align-items-end mb-2">
					<div class="col">
						<input type="text" name="part_name[]" class="form-control" placeholder="Prodotto" value="${partName}" required>
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

		$(document).on('click', '.removePartBtn', function() {
			$(this).closest('.form-row').remove();
		});

		$('#addWalletDataForm').submit(function(e) {
			e.preventDefault();

			// Calcola il costo totale della spesa
			var totalExpense = parseFloat($('#addWalletDataAmount').val()) || 0;
			var partsSum = 0;
			
			// Somma i costi di ogni parte
			$('input[name="part_cost[]"]').each(function(){
				partsSum += parseFloat($(this).val()) || 0;
			});

			// Verifica che la somma dei costi delle parti non superi il costo totale
			if (partsSum > totalExpense) {
				$('#addWalletDataStatus').html('<div class="alert alert-danger">La somma dei costi dei prodotti non può superare il costo totale della spesa.</div>');
				return;
			}

			var formData = new FormData($('#addWalletDataForm')[0]);

			$.ajax({
				url: 'addWalletData.php',
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				success: function(response) {
					if (response.status === 'success') {
						$('#addWalletDataStatus').html('<div class="alert alert-success">Spesa aggiunta con successo!</div>');
						setTimeout(function() { window.location.reload(); }, 1000);
					} else {
						$('#addWalletDataStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
					}
				},
				error: function() {
					$('#addWalletDataStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
				}
			});
		});

		// Mostra il nome del file selezionato nell'input
		$('#addWalletDataAttachment').on('change', function() {
			var fileName = $(this).val().split('\\').pop();
			$('#fileNameDisplay').val(fileName);
		});
	});
</script>

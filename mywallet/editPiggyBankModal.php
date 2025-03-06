<!-- Modale per la modifica di una voce al salvadanaio -->
<div class="modal fade" id="editPiggyBankModal" tabindex="-1" aria-labelledby="editPiggyBankModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPiggyBankModalLabel">Modifica voce del salvadanaio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editPiggyBankStatus"></div>
                <form id="editPiggyBankForm">
                    <input type="hidden" name="id" id="editPiggyBankId">
                    <div class="form-group">
                        <label for="editPiggyBankName">Descrizione</label>
                        <input type="text" name="name" id="editPiggyBankName" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="editPiggyBankAmount">Totale</label>
                        <input type="number" name="amount" id="editPiggyBankAmount" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="editPiggyBankDate">Data</label>
                        <input type="date" name="added_date" id="editPiggyBankDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		// Gestisco la modifica di un importo al salvadanaio
		$('#editPiggyBankModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');
			var name = button.data('name');
			var amount = button.data('amount');
			var date = button.data('date');
		
			$('#editPiggyBankId').val(id);
			$('#editPiggyBankName').val(name);
			$('#editPiggyBankAmount').val(amount);
			$('#editPiggyBankDate').val(date);
		});
		
		$('#editPiggyBankForm').submit(function(e) {
			e.preventDefault();
			$.ajax({
				url: 'editPiggyBank.php',
				type: 'POST',
				data: $(this).serialize(),
				success: function(response) {
					if (response.status === 'success') {
						$('#editPiggyBankStatus').html('<div class="alert alert-success">' + response.message + '</div>');
						setTimeout(function() { window.location.reload(); }, 1000);
					} else {
						$('#editPiggyBankStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
					}
				},
				error: function() {
					$('#editPiggyBankStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
				}
			});
		});
    });
</script>
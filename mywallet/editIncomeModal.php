<!-- Modale per la modifica di un'entrata -->
<div class="modal fade" id="editIncomeModal" tabindex="-1" aria-labelledby="editIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIncomeModalLabel">Modifica entrata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editIncomeStatus"></div>
                <form id="editIncomeForm">
                    <input type="hidden" name="id" id="editIncomeId">
                    <div class="form-group">
                        <label for="editIncomeName">Descrizione</label>
                        <input type="text" name="name" id="editIncomeName" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="editIncomeAmount">Totale</label>
                        <input type="number" name="amount" id="editIncomeAmount" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="editIncomeDate">Data</label>
                        <input type="date" name="added_date" id="editIncomeDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		// Gestisco la modifica di un'entrata
		$('#editIncomeModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');
			var name = button.data('name');
			var amount = button.data('amount');
			var date = button.data('date');
		
			$('#editIncomeId').val(id);
			$('#editIncomeName').val(name);
			$('#editIncomeAmount').val(amount);
			$('#editIncomeDate').val(date);
		});
		
		$('#editIncomeForm').submit(function(e) {
			e.preventDefault();
			$.ajax({
				url: 'editIncome.php',
				type: 'POST',
				data: $(this).serialize(),
				success: function(response) {
					if (response.status === 'success') {
						$('#editIncomeStatus').html('<div class="alert alert-success">' + response.message + '</div>');
						setTimeout(function() { window.location.reload(); }, 1000);
					} else {
						$('#editIncomeStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
					}
				},
				error: function() {
					$('#editIncomeStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
				}
			});
		});
    });
</script>
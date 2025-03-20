<!-- Modale per l'aggiunta di una spesa extra -->
<div class="modal fade" id="addExtraExpenseModal" tabindex="-1" aria-labelledby="addExtraExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addExtraExpenseModalLabel">Aggiungi spesa extra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="addExtraExpenseStatus"></div>
                <form id="addExtraExpenseForm">
                    <div class="form-group">
                        <label for="addExtraExpenseName">Descrizione</label>
                        <input type="text" name="name" id="addExtraExpenseName" class="form-control" placeholder="Descrizione" required>
                    </div>
                    <div class="form-group">
                        <label for="addExtraExpenseAmount">Totale</label>
                        <input type="number" name="amount" id="addExtraExpenseAmount" class="form-control" step="0.01" placeholder="Totale" required>
                    </div>
                    <div class="form-group">
                        <label for="addExtraExpenseDate">Data</label>
                        <input type="date" name="debit_date" id="addExtraExpenseDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#addExtraExpenseForm').submit(function(e) {
		e.preventDefault();
		$('button[type="submit"]').prop('disabled', true);
			$.ajax({
				url: 'addExtraExpense.php',
				type: 'POST',
				data: $(this).serialize(),
				success: function(response) {
				if(response.status === 'success') {
			$('#addExtraExpenseStatus').html('<div class="alert alert-success">Spesa aggiunta con successo!</div>');
			$('#addExtraExpenseForm')[0].reset();
			setTimeout(function() { window.location.reload(); }, 1000);
			} else {
			$('#addExpenseStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
			}
			$('button[type="submit"]').prop('disabled', false);
		},

		error: function() {
			$('#addExtraExpenseStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
			$('button[type="submit"]').prop('disabled', false); // Re-enable submit button
				}
			});
		});
    });
</script>
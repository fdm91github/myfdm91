<!-- Modale per l'aggiunta di una spesa ricorrente -->
<div class="modal fade" id="addRecurringExpenseModal" tabindex="-1" role="dialog" aria-labelledby="addRecurringExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRecurringExpenseModalLabel">Aggiungi spesa ricorrente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="addRecurringExpenseStatus"></div>
                <form id="addRecurringExpenseForm">
                    <input type="hidden" name="id" id="editEstimatedExpenseId">
					<div class="form-group">
                        <label for="addRecurringExpenseName">Descrizione</label>
                        <input type="text" name="name" id="addRecurringExpenseName" class="form-control" placeholder="Descrizione" required>
                    </div>
                    <div class="form-group">
                        <label for="addRecurringExpenseAmount">Totale</label>
                        <input type="number" name="amount" id="addRecurringExpenseAmount" class="form-control" step="0.01" placeholder="Totale" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="addRecurringExpenseStartMonth">Mese di inizio</label>
                            <select name="start_month" id="addRecurringExpenseStartMonth" class="form-control" required>
                                <option value="" disabled selected>Seleziona il mese</option>
                                <option value="1">Gennaio</option>
                                <option value="2">Febbraio</option>
                                <option value="3">Marzo</option>
                                <option value="4">Aprile</option>
                                <option value="5">Maggio</option>
                                <option value="6">Giugno</option>
                                <option value="7">Luglio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Settembre</option>
                                <option value="10">Ottobre</option>
                                <option value="11">Novembre</option>
                                <option value="12">Dicembre</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="addRecurringExpenseStartYear">Anno di inizio</label>
                            <input type="number" name="start_year" id="addRecurringExpenseStartYear" class="form-control" min="1900" max="2100" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="addRecurringExpenseEndMonth">Mese di fine</label>
                            <select name="end_month" id="addRecurringExpenseEndMonth" class="form-control">
                                <option value="" disabled selected>Seleziona il mese</option>
                                <option value="1">Gennaio</option>
                                <option value="2">Febbraio</option>
                                <option value="3">Marzo</option>
                                <option value="4">Aprile</option>
                                <option value="5">Maggio</option>
                                <option value="6">Giugno</option>
                                <option value="7">Luglio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Settembre</option>
                                <option value="10">Ottobre</option>
                                <option value="11">Novembre</option>
                                <option value="12">Dicembre</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="addRecurringExpenseEndYear">Anno di fine</label>
                            <input type="number" name="end_year" id="addRecurringExpenseEndYear" class="form-control" min="1900" max="2100">
                        </div>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="undetermined" id="addRecurringExpenseUndetermined" class="form-check-input" onclick="toggleRecurringEndDate('add')">
                        <label class="form-check-label" for="addRecurringExpenseUndetermined">Indeterminato</label>
                    </div>
                    <div class="form-group">
                        <label for="addRecurringExpenseDebitDate">Data di addebito</label>
                        <input type="number" name="debit_date" id="addRecurringExpenseDebitDate" class="form-control" min="1" max="31" required>
                    </div>
                    <div class="form-group">
                        <label for="addRecurringExpenseBillingFrequency">Frequenza di fatturazione (mesi)</label>
                        <input type="number" name="billing_frequency" id="addRecurringExpenseBillingFrequency" class="form-control" min="1" max="12" value="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" id="addRecurringExpenseSubmitButton">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleRecurringEndDate(action) {
        var endMonth = document.getElementById(action + 'RecurringExpenseEndMonth');
        var endYear = document.getElementById(action + 'RecurringExpenseEndYear');
        if (document.getElementById(action + 'RecurringExpenseUndetermined').checked) {
            endMonth.disabled = true;
            endYear.disabled = true;
        } else {
            endMonth.disabled = false;
            endYear.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var today = new Date();
        var currentMonth = today.getMonth() + 1; // Gennaio è 0
        var currentYear = today.getFullYear();

        document.getElementById('addRecurringExpenseStartMonth').value = currentMonth;
        document.getElementById('addRecurringExpenseStartYear').value = currentYear;
        document.getElementById('addRecurringExpenseEndMonth').value = currentMonth;
        document.getElementById('addRecurringExpenseEndYear').value = currentYear;
    });

    $(document).ready(function() {
        $('#addRecurringExpenseForm').submit(function(e) {
            e.preventDefault();

            var startMonth = parseInt($('#addRecurringExpenseStartMonth').val());
            var startYear = parseInt($('#addRecurringExpenseStartYear').val());
            var endMonth = parseInt($('#addRecurringExpenseEndMonth').val());
            var endYear = parseInt($('#addRecurringExpenseEndYear').val());
            var billingFrequency = parseInt($('#addRecurringExpenseBillingFrequency').val());
            var undetermined = $('#addRecurringExpenseUndetermined').prop('checked');

			// Controllo che la data di inizio sia antecedente a quella di fine
            if (!undetermined && (endYear < startYear || (endYear === startYear && endMonth < startMonth))) {
                $('#addRecurringExpenseStatus').html('<div class="alert alert-danger">La data di fine non può essere antecedente alla data di inizio.</div>');
                return;
            }

			// Controllo che la frequenza di fatturazione sia congruente con i mesi inseriti
            if (!undetermined) {
                var totalMonths = (endYear - startYear) * 12 + (endMonth - startMonth +1);
                if (totalMonths < 0 || totalMonths % billingFrequency !== 0) {
                    $('#addRecurringExpenseStatus').html('<div class="alert alert-danger">La data di fine non combacia con la frequenza di fatturazione.</div>');
                    return;
                }
            }

            $.ajax({
                url: 'addRecurringExpense.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#addRecurringExpenseStatus').html('<div class="alert alert-success">Spesa aggiunta con successo!</div>');
                    $('#addRecurringExpenseForm')[0].reset();
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#addRecurringExpenseStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>

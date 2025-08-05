<!-- /* Search dropdown menu, and the associated content. */ -->
<div class="collapse aletho-dropdown-body" id="customSearchDropdown">
    <div class="aletho-container" id="search-cont">
        <label class="aletho-labels" for="search-options">Boek zoeken:</label>
        <form class="needs-validation" id="search-form" novalidate>
            <select class="aletho-inputs search-select form-select-sm" name="search-method" id="search-options">
                <option value="title">Titel</option>
                <option value="writer">Schrijver</option>
                <option value="genre">Genre</option>
            </select>
            <input class="aletho-inputs search-input form-select-sm mb-2" id="search-inp" type="text" name="searchInp" placeholder="Zoek op titel ..." required>
        </form>
    </div>
</div>
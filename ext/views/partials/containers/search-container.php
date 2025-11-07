<form class="search-form" id="search-form" novalidate>
    <select class="aletho-inputs" name="search-method" id="search-options">
        <option value="title">Titel</option>
        <option value="writer">Schrijver</option>
        <option value="genre">Genre</option>
    </select>

    <div class="aletho-input-wrapper">
        <input  class="aletho-inputs"
                id="search-inp"
                type="text"
                name="searchInp"
                placeholder="Zoek op titel ..."
                required>
        <span id="clear-search-icon" class="clear-search-icon">×</span>
    </div>
</form>
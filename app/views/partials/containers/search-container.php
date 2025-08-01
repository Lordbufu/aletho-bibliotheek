<!-- /* Search dropdown menu, and the associated content. */ -->
<div class="secundary-color-1 collapse text-center p-1 pt-2 pb-2 bg-sec-col border border-top-0 border-dark rounded-bottom" id="customSearchDropdown">
    <div class="search-container row m-0 p-0" id="search-cont">
        <label class="search-option-label mb-1" for="search-options">Boek zoeken:</label>
        <form class="needs-validation row p-0 m-0" id="search-form" novalidate>
            <select class="search-options text-center col" name="search-method" id="search-options">
                <option value="title">Titel</option>
                <option value="writer">Schrijver</option>
                <option value="genre">Genre</option>
            </select>
            <input class="search-inp rounded ms-1 col" id="search-inp" type="text" name="searchInp" placeholder="Zoek op titel ..." required>
        </form>
    </div>
</div>
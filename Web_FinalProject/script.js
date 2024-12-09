document.addEventListener("DOMContentLoaded", function () {
    const memberFieldsContainer = document.getElementById("memberFields");

    memberFieldsContainer.addEventListener("click", function (e) {
        if (e.target.classList.contains("add-member")) {
            const memberCount = memberFieldsContainer.querySelectorAll("input[name='member_names[]']").length;
            const newField = document.createElement("div");
            newField.classList.add("mb-3", "d-flex", "align-items-center");
            newField.innerHTML = `
                <label for="memberName${memberCount + 1}" class="form-label"> </label>
                <input
                    type="text"
                    class="form-control ms-2"
                    id="memberName${memberCount + 1}"
                    name="member_names[]"
                    placeholder="請輸入組員姓名"
                    required
                >
                <button type="button" class="btn btn-outline-danger ms-2 remove-member">x</button>
            `;
            memberFieldsContainer.appendChild(newField);
        }
    });

    memberFieldsContainer.addEventListener("click", function (e) {
        if (e.target.classList.contains("remove-member")) {
            e.target.parentElement.remove();
        }
    });
});

function getPlaces(query, callback) {
    $.get(`https://api-adresse.data.gouv.fr/search/?q=${query}&type=street`, callback)
}

export function initAutocompletePlace(selector, resultSelector, onSelect) {
    $(document).ready(() => {
        const input = $(selector)
        const result = $(resultSelector);

        const searchPlace = (query) => {
            if (!query) {
                result.html('<li class="location-item collection-item">No result</li>')
                return;
            }

            getPlaces(query, (data) => {
                const collection = data ? data['features'] : [];

                if (collection.length === 0) {
                    result.html('<li class="location-item collection-item">No result</li>')
                    return;
                }

                const list = collection.map(c =>{
                    const props = c['properties'];
                    return `<li id="${props['id']}" class="location-item collection-item">${props['label']}</li>`
                });

                result.html(list.concat())

                $('.location-item').on('click', ($event) => {
                    const id = $event.target.id;
                    const r = collection.find(s => s['properties']['id'] === id);

                    if (onSelect) {
                        onSelect(r, input, result);
                    }
                })
            });
        }

        window.addEventListener('click', ($event) => {
            if ($event.target === result || $event.target === input) {
                return;
            }
            result.css('visibility', 'hidden')
        })

        input.on('input', ($event) => {
            const query = $event.target.value;
            searchPlace(query);
            result.css('visibility', 'visible')
        })
    });
}
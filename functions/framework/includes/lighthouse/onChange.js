// Add event listener to all input elements within 'metrics-table input'
document.querySelectorAll('.metrics-table input').forEach(element => {
    
    element.addEventListener('change', (e) =>  {

        // Get the data from 'name' and 'value' fields, for example from a form
        const name = encodeURIComponent(element.name); // Assuming 'element' holds the 'name' value
        const value = encodeURIComponent(element.value); // Assuming 'element' holds the 'value' value

        // Create a URLSearchParams object to handle the data
        const data = new URLSearchParams();
        data.append('action', 'get_lighthouse_report');
        data.append('name', name);
        data.append('value', value);

        // Use fetch to send the POST request
        fetch(base_wp_api.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded' // Send data as form data
            },
            body: data.toString() // Convert the FormData object to a string
        })
        .then(response => {
            if (response.ok) {
                return response.json(); // Parse the response as JSON
            }
            throw new Error('Request failed');
        })
        .then(data => {
            // Handle the response
            console.log('Response received:', data);
        })
        .catch(error => {
            // Handle any errors in the request
            console.error('There was a problem with the request:', error);
        });

        window.location.reload();

    });
});

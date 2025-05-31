

    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr'],
            datasets: [{
                label: 'Revenue',
                data: [12000, 19000, 3000, 5000],
                backgroundColor: '#76ff03',
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#fff'
                    }
                },
                x: {
                    ticks: {
                        color: '#fff'
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#fff'
                    }
                }
            }
        }
    });

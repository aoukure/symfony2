<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookstoreController extends AbstractController
{
    private array $customers = [
        ['id' => 1, 'name' => 'Анна', 'email' => 'anna@example.com'],
        ['id' => 2, 'name' => 'Борис', 'email' => 'boris@example.com'],
        ['id' => 3, 'name' => 'Карина', 'email' => 'karina@example.com'],
        ['id' => 4, 'name' => 'Дмитрий', 'email' => 'dmitry@example.com'],
    ];

    private array $books = [
        ['id' => 1, 'title' => 'Война и мир', 'price' => 1200],
        ['id' => 2, 'title' => '1984', 'price' => 500],
        ['id' => 3, 'title' => 'Мастер и Маргарита', 'price' => 800],
        ['id' => 4, 'title' => 'Гарри Поттер', 'price' => 900],
        ['id' => 5, 'title' => 'Атлант расправил плечи', 'price' => 1500],
    ];

    private array $orders = [
        ['id' => 1, 'customer_id' => 1, 'book_ids' => [1, 2]],
        ['id' => 2, 'customer_id' => 1, 'book_ids' => [3]],
        ['id' => 3, 'customer_id' => 2, 'book_ids' => [5, 4]],
        ['id' => 4, 'customer_id' => 3, 'book_ids' => [1, 5]],
        ['id' => 5, 'customer_id' => 2, 'book_ids' => [3, 4]],
        ['id' => 6, 'customer_id' => 4, 'book_ids' => [2]],
    ];

    #[Route('/', name: 'store_index')]
    public function index(): Response
    {
        $bookById = [];
        foreach ($this->books as $book) {
            $bookById[$book['id']] = $book;
        }

        $customerById = [];
        foreach ($this->customers as $customer) {
            $customerById[$customer['id']] = $customer;
        }

        $ordersWithTotals = [];
        foreach ($this->orders as $order) {
            $total = 0;
            $titles = [];
            foreach ($order['book_ids'] as $bookId) {
                if (isset($bookById[$bookId])) {
                    $total += $bookById[$bookId]['price'];
                    $titles[] = $bookById[$bookId]['title'];
                }
            }
            $ordersWithTotals[] = [
                'id' => $order['id'],
                'customer' => $customerById[$order['customer_id']]['name'] ?? '—',
                'titles' => $titles,
                'total' => $total,
            ];
        }

        $customerOrderCounts = [];
        foreach ($this->customers as $customer) {
            $count = 0;
            foreach ($this->orders as $order) {
                if ($order['customer_id'] === $customer['id']) {
                    $count++;
                }
            }
            $customerOrderCounts[] = [
                'name' => $customer['name'],
                'email' => $customer['email'],
                'orders_count' => $count,
            ];
        }

        $spentByCustomer = [];
        foreach ($this->customers as $customer) {
            $spentByCustomer[$customer['id']] = 0;
        }
        foreach ($this->orders as $order) {
            $total = 0;
            foreach ($order['book_ids'] as $bookId) {
                if (isset($bookById[$bookId])) {
                    $total += $bookById[$bookId]['price'];
                }
            }
            $spentByCustomer[$order['customer_id']] += $total;
        }
        $topCustomers = [];
        foreach ($this->customers as $customer) {
            $topCustomers[] = [
                'name' => $customer['name'],
                'total' => $spentByCustomer[$customer['id']],
            ];
        }
        usort($topCustomers, fn ($a, $b) => $b['total'] <=> $a['total']);
        $topCustomers = array_slice($topCustomers, 0, 3);

        $orderTotals = array_map(fn ($o) => $o['total'], $ordersWithTotals);
        $averageOrder = count($orderTotals) > 0
            ? array_sum($orderTotals) / count($orderTotals)
            : 0;

        $mostExpensive = null;
        foreach ($this->books as $book) {
            if ($mostExpensive === null || $book['price'] > $mostExpensive['price']) {
                $mostExpensive = $book;
            }
        }

        return $this->render('store/index.html.twig', [
            'customers' => $customerOrderCounts,
            'orders' => $ordersWithTotals,
            'topCustomers' => $topCustomers,
            'averageOrder' => $averageOrder,
            'mostExpensiveBook' => $mostExpensive,
        ]);
    }
}

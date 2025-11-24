export const metadata = {
  title: "Officer Report Details",
};

export default function Page({ params }: { params: { id: string } }) {
  return (
    <main>
      <h1>Officer Report Details</h1>
      <p>Detailed view of a selected report. Currently viewing record {params.id}.</p>
    </main>
  );
}
